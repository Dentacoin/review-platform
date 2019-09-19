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

        $dentist = $this->dentist ? $this->dentist : $this->clinic;

        if ($dentist->hasimage) {
            $img->insert( public_path().'/img-trp/new-cover-review.png');

            $avatar_image_dentist = Image::make( $dentist->getImagePath(true) );
            $avatar_image_dentist->resize(320, 320);
            $img->insert( $avatar_image_dentist , 'top-left', 70, 190 );

        } else {
            $img->insert( public_path().'/img-trp/new-cover-review-no-avatar.png');
        }

        $dentist_name = $dentist->name;

        $img->text($dentist_name, 225, 547, function($font) {
            $font->file(public_path().'/fonts/Calibri-Bold.ttf');
            $font->size(33);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('top');
        });


        if( $this->title ) {
            //$img->insert( public_path().'/img-trp/cover-review.png');
            $title = $this->title;
            //$title = 'ale ale ale';
            $title = wordwrap('“'.$title.'”', 26); 
            $lines = count(explode("\n", $title));
            $top = 160;
            if ($lines == 1) {
                $top = 180;
            } else if($lines == 2) {
                $top -= $lines*15;
            } else if($lines == 3) {
                $top -= $lines*35;
            } else {
                $top -= $lines*35;
            }
            
            $img->text($title, 573, $top, function($font) {
                $font->file(public_path().'/fonts/Calibri-Bold.ttf');
                $font->size(45);
                $font->color('#000000');
                $font->align('left');
                $font->valign('top');
            });

            $voffset = (3 - $lines)*10;
        } else {
            $voffset = 0;
        }


        $answer = $this->answer;
        $answer = mb_strlen($answer)>100 ? mb_substr($answer, 0, 97).'...' : $answer;
        $answer = wordwrap('"'.$answer.'"', 38);
        $lines = count(explode("\n", $answer));
        $top = 355;
        $top -= $lines*38;

        $img->text($answer, 573, $top - $voffset, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(35);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });

        if ($this->user->hasimage) {
            $avatar_image = Image::make( $this->user->getImagePath(true) );
            $avatar_image->resize(60, 60);
            $avatar = Image::canvas(60, 60, '#fff');
            $avatar->insert( $avatar_image, 'top-left', 0, 0 );
            $avatar->insert( public_path().'/img-trp/cover-avatar-mask-new.png' , 'top-left', 0, 0 );
            $img->insert( $avatar , 'top-left', 706, 500 );

            $left_patient_name = 774;
        } else {
            $left_patient_name = 710;
        }

        if ($this->verified) {
            $img->insert( public_path().'/img-trp/cover-trusted.png' , 'top-left', 921, 424 );
        }


        $names = $this->user->getName();
        $img->text($names, $left_patient_name, 516, function($font) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size(30);
            $font->color('#000000');
            $font->align('left');
            $font->valign('top');
        });


        $step = 67;
        $startX = 573;
        for($i=1;$i<=5;$i++) {
            $img->insert( public_path().'/img-trp/cover-star-review-new-gray.png' , 'top-left', $startX, 409 );
            $startX += $step;
        }

        $step = 67;
        $startX = 573;
        for($i=1;$i<=$this->rating;$i++) {
            $img->insert( public_path().'/img-trp/cover-star-review-new.png' , 'top-left', $startX, 409 );
            $startX += $step;
        }

        $rest = ( $this->rating - floor( $this->rating ) );
        if($rest) {
            $halfstar = Image::canvas(60*$rest, 61, '#fff');
            $halfstar->insert( public_path().'/img-trp/cover-star-review-new.png', 'top-left', 0, 0 );
            $img->insert($halfstar , 'top-left', $startX, 409 );
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