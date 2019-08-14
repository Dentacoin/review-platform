<?php

namespace App\Models;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Dcn;
use App\Models\User;
use App\Models\Secret;
use App\Models\Reward;
use App\Models\DcnReward;
use App\Models\ReviewAnswer;

use Image;

class Review extends Model {
    
    use SoftDeletes;
    
    
    protected $fillable = [
        'user_id',
        'dentist_id',
        'clinic_id',
        'rating',
        'youtube_id',
        'youtube_approved',
        'title',
        'answer',
        'reply',
        'verified',
        'upvotes',
        'secret_id',
        'status',
        'treatments',
        'reward_address',
        'reward_tx',
        'ipfs',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function secret() {
        return $this->hasOne('App\Models\Secret', 'id', 'secret_id')->withTrashed();
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
    
    public function dentist() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id')->withTrashed();
    }
    
    public function clinic() {
        return $this->hasOne('App\Models\User', 'id', 'clinic_id')->withTrashed();
    }

    public function answers() {
        return $this->hasMany('App\Models\ReviewAnswer', 'review_id', 'id')->with('question');
    }

    public function upvotes() {
        return $this->hasMany('App\Models\ReviewUpvote', 'review_id', 'id');
    }
    
    public function downvotes() {
        return $this->hasMany('App\Models\ReviewDownvote', 'review_id', 'id');
    }

    public function afterSubmitActions() {

        if( $this->dentist ) {
            $this->dentist->sendTemplate( $this->verified ? 21 : 6, [
                'review_id' => $this->id,
            ]);            
        }
        if( $this->clinic ) {
            $this->clinic->sendTemplate( $this->verified ? 21 : 6, [
                'review_id' => $this->id,
            ]);            
        }

        if($this->verified) {
            $reward = new DcnReward();
            $reward->user_id = $this->dentist_id ? $this->dentist_id : $this->clinic_id;
            $reward->platform = 'trp';
            $reward->reward = Reward::getReward('reward_dentist');
            $reward->type = 'dentist-review';
            $reward->reference_id = $this->id;

            $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
            $dd = new DeviceDetector($userAgent);
            $dd->parse();

            if ($dd->isBot()) {
                // handle bots,spiders,crawlers,...
                $reward->device = $dd->getBot();
            } else {
                $reward->device = $dd->getDeviceName();
                $reward->brand = $dd->getBrandName();
                $reward->model = $dd->getModel();
                $reward->os = $dd->getOs()['name'];
            }

            $reward->save();
        }


        if($this->user->invited_by && !$this->user->invitor->is_dentist) {
            $inv = UserInvite::where('user_id', $this->user->invited_by)->where('invited_id', $this->user->id)->first();
            if(!empty($inv) && !$inv->rewarded) {

                $reward = new DcnReward();
                $reward->user_id = $this->user->invitor;
                $reward->platform = 'trp';
                $reward->reward = Reward::getReward('reward_invite');
                $reward->type = 'invitation';
                $reward->reference_id = $inv->id;

                $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                $dd = new DeviceDetector($userAgent);
                $dd->parse();

                if ($dd->isBot()) {
                    // handle bots,spiders,crawlers,...
                    $reward->device = $dd->getBot();
                } else {
                    $reward->device = $dd->getDeviceName();
                    $reward->brand = $dd->getBrandName();
                    $reward->model = $dd->getModel();
                    $reward->os = $dd->getOs()['name'];
                }

                $reward->save();
                
                $this->user->invitor->sendTemplate( 22, [
                    'who_joined_name' => $this->user->getName()
                ] );
            }
        }

        if( $this->dentist ) {
            $this->dentist->recalculateRating();
        }
        if( $this->clinic ) {
            $this->clinic->recalculateRating();
        }
    }

    public function generateSocialCover() {

        $path = $this->getSocialCoverPath();

        $img = Image::canvas(1200, 628, '#fff');

        if( $this->title ) {
            $img->insert( public_path().'/img-trp/cover-review.png');
            $title = $this->title;
            //$title = 'ale ale ale';
            $title = wordwrap('“'.$title.'”', 40); 
            $lines = count(explode("\n", $title));
            $top = 91;
            $top -= $lines*38;
            $img->text($title, 123, $top, function($font) {
                $font->file(public_path().'/fonts/Calibri-Italic.ttf');
                $font->size(60);
                $font->color('#000000');
                $font->align('left');
                $font->valign('top');
            });

            $voffset = (3 - $lines)*40;
        } else {
            $img->insert( public_path().'/img-trp/cover-review-notitle.png');
            $voffset = 0;
        }


        $answer = $this->answer;
        $answer = mb_strlen($answer)>170 ? mb_substr($answer, 0, 167).'...' : $answer;
        $answer = wordwrap('"'.$answer.'"', 60);
        $lines = count(explode("\n", $answer));
        $top = 335;
        $top -= $lines*38;

        $img->text($answer, 218, $top - $voffset, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(38);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });

        $avatar_image = Image::make( $this->user->hasimage ? $this->user->getImagePath(true) : public_path().'/new-vox-img/no-avatar-0.png' );
        $avatar_image->resize(70, 70);
        $avatar = Image::canvas(70, 70, '#fff');
        $avatar->insert( $avatar_image, 'top-left', 0, 0 );
        $avatar->insert( public_path().'/img-trp/cover-avatar-mask.png' , 'top-left', 0, 0 );
        $img->insert( $avatar , 'top-left', 123, 280 - $voffset );



        $names = 'by: '.$this->user->getName();
        $img->text($names, 506, 472, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(38);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });


        $step = 73;
        $start = 122;
        for($i=1;$i<=$this->rating;$i++) {
            $img->insert( public_path().'/img-trp/cover-star-review.png' , 'top-left', $start, 450 );
            $start += $step;
        }

        $rest = ( $this->rating - floor( $this->rating ) );
        if($rest) {
            $halfstar = Image::canvas(65*$rest, 67, '#fff');
            $halfstar->insert( public_path().'/img-trp/cover-star-review.png', 'top-left', 0, 0 );
            $img->insert($halfstar , 'top-left', $start, 450 );
        }

        $img->save($path);
    }

    public function getSocialCoverPath() {
        $folder = storage_path().'/app/public/reviews/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'-cover.jpg';
    }
    public function getSocialCover() {
        if(!$this->hasimage_social) {
            $this->generateSocialCover();
        }
        return url('/storage/reviews/'.($this->id%100).'/'.$this->id.'-cover.jpg').'?rev='.$this->updated_at->timestamp;
    }

    public static function handleTreatmentTooltips($t) {
        $new_title = str_replace("[/]","</span>",$t);
        
        return preg_replace('/\[([^\]]*)\]/', '<span class="tooltip-text" text="${1}">', $new_title);
    }

    public function getTreatmentsAttribute() {
        return json_decode($this->attributes['treatments'], true);
    }
    public function setTreatmentsAttribute($value) {
        $this->attributes['treatments'] = $value ? json_encode($value) : '';
    }
}

?>