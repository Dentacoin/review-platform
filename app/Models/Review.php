<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Helpers\GeneralHelper;

use App\Models\DcnReward;
use App\Models\Reward;
use App\Models\User;

use Image;

class Review extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'dentist_id',
        'clinic_id',
        'review_to_id',
        'team_own_practice',
        'rating',
        "team_doctor_rating",
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

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
    
    public function dentist() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id')->withTrashed();
    }
    
    public function original_dentist() {
        return $this->hasOne('App\Models\User', 'id', 'review_to_id')->withTrashed();
    }
    
    public function clinic() {
        return $this->hasOne('App\Models\User', 'id', 'clinic_id')->withTrashed();
    }

    public function answers() {
        return $this->hasMany('App\Models\ReviewAnswer', 'review_id', 'id')
        ->with(['question', 'question.translations']);
    }

    public function upvotes() {
        return $this->hasMany('App\Models\ReviewUpvote', 'review_id', 'id');
    }
    
    public function downvotes() {
        return $this->hasMany('App\Models\ReviewDownvote', 'review_id', 'id');
    }

    public function getDentist($current_dentist = null) {
        if($current_dentist) {
            return $current_dentist;
        } else {
            return !empty($this->review_to_id) ? $this->original_dentist : ($this->dentist ? $review->dentist : $this->clinic);
        }
    }

    public function afterSubmitActions() {
        //sent email to the dentist/clinic
        $this->original_dentist->sendTemplate( $this->verified ? 21 : 6, [
            'review_id' => $this->id,
            'dentist_id' => $this->review_to_id,
        ], 'trp');            

        if($this->verified) {
            $reward = new DcnReward();
            $reward->user_id = $this->review_to_id;
            $reward->platform = 'trp';
            $reward->reward = Reward::getReward('reward_dentist');
            $reward->type = 'dentist-review';
            $reward->reference_id = $this->id;
            GeneralHelper::deviceDetector($reward);
            $reward->save();

            $dent_id = $this->review_to_id;
            $reviews = self::where(function($query) use ($dent_id) {
                $query->where( 'dentist_id', $dent_id)->orWhere('clinic_id', $dent_id);
            })->where('user_id', $this->user_id)
            ->get();

            if ($reviews->count()) {
                
                foreach ($reviews as $review) {
                    //give reward to the patient for trusted reviews
                    if(empty($review->verified)) {
                        $review->verified = true;
                        $review->save();

                        $reward = new DcnReward();
                        $reward->user_id = $this->user_id;
                        $reward->platform = 'trp';
                        $reward->reward = Reward::getReward('review_trusted');
                        $reward->type = 'review_trusted';
                        $reward->reference_id = $this->id;
                        $reward->save();
                    }
                }
            }
        }

        $this->user->giveInvitationReward('trp');
        $this->original_dentist->recalculateRating();
    }

    public function generateSocialCover($d_id) {

        $path = $this->getSocialCoverPath($d_id);
        $img = Image::canvas(1200, 628, '#fff');
        $dentist = User::find($d_id);

        if ($dentist->hasimage) {
            $img->insert( public_path().'/img-trp/new-cover-review.png');

            $avatar_image_dentist = Image::make( $dentist->getImagePath(true) );
            $avatar_image_dentist->resize(302, 302);

            $avatar_mask = Image::canvas(302, 302, '#fff');
            $avatar_mask->insert( $avatar_image_dentist, 'top-left', 0, 0 );
            $avatar_mask->insert( public_path().'/img-trp/new-dentist-mask-review.png' , 'top-left', 0, 0 );

            $img->insert( $avatar_mask , 'top-left', 78, 199 );

        } else {
            $img->insert( public_path().'/img-trp/new-cover-review-no-avatar.png');
        }

        $dentist_name = $dentist->name;
        $dentist_name = wordwrap($dentist_name, 27); 
        $lines = count(explode("\n", $dentist_name));
        $top = 547;

        if($lines == 2) {
            $top -= 20;
        }

        $img->text($dentist_name, 225, $top, function($font) {
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

            $size = 45;
            if ($lines > 3) {
                $size = 40;
            }
            
            $img->text($title, 573, $top, function($font) use ($size) {
                $font->file(public_path().'/fonts/Calibri-Bold.ttf');
                $font->size($size);
                $font->color('#000000');
                $font->align('left');
                $font->valign('top');
            });

            $voffset = (3 - $lines)*10;
            $title = true;
        } else {
            $voffset = 0;
            $title = false;
        }

        $answer = trim(preg_replace('/\s\s+/', ' ', $this->answer));
        $answer = mb_strlen($answer)>100 ? mb_substr($answer, 0, 97).'...' : $answer;
        $answer = wordwrap('"'.$answer.'"', 38);
        $lines = count(explode("\n", $answer));
        $top = 355;
        $top -= $lines*38;

        if (empty($title) && $lines < 3) {
            $font_size = 40;
        } else {
            $font_size = 35;
        }

        $img->text($answer, 573, $title ? $top - $voffset : 260 - ($lines * 30), function($font) use ($font_size) {
            $font->file(public_path().'/fonts/Calibri.ttf');
            $font->size($font_size);
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
            $img->insert( public_path().'/img-trp/cover-trusted.png' , 'top-left', 921, $title ? 424 : 405 );
        }

        $names = $this->user->getNames();
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
            $img->insert( public_path().'/img-trp/cover-star-review-new-gray.png' , 'top-left', $startX, $title ? 409 : 390 );
            $startX += $step;
        }

        $step = 67;
        $startX = 573;

        $item_rating = !empty($this->team_doctor_rating) && ($d_id == $this->dentist_id) ? $this->team_doctor_rating : $this->rating;

        for($i=1;$i<=$item_rating;$i++) {
            $img->insert( public_path().'/img-trp/cover-star-review-new.png' , 'top-left', $startX, $title ? 409 : 390 );
            $startX += $step;
        }

        $rest = ( $item_rating - floor( $item_rating ) );
        if($rest) {
            $halfstar = Image::canvas(60*$rest, 61, '#fff');
            $halfstar->insert( public_path().'/img-trp/cover-star-review-new.png', 'top-left', 0, 0 );
            $img->insert($halfstar , 'top-left', $startX, $title ? 409 : 390 );
        }

        $img->save($path);
        $this->hasimage_social = true;
        $this->save();
    }

    public function getSocialCoverPath($d_id) {
        $folder = storage_path().'/app/public/reviews/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'-cover.jpg';
    }

    public function getSocialCover($d_id) {
        if(!$this->hasimage_social) {
            $this->generateSocialCover($d_id);
        }
        return url('/storage/reviews/'.($this->id%100).'/'.$this->id.'-cover.jpg').'?rev='.$this->updated_at->timestamp.'12';
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