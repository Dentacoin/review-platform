<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Review;
use Mail;
use Session;

class Email extends Model
{
    use SoftDeletes;

    protected $fillable = [
    	"user_id",
    	"template_id",
    	"meta",
    	"sent",
	];

	public function template() {
        return $this->hasOne('App\Models\EmailTemplate', 'id', 'template_id');		
	}

	public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');		
	}

	private $button_style = 'style="text-decoration:none;background:#126585;color:#FFFFFF;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;text-transform:none;margin:0px;border:none;border-radius:5px;color:#FFFFFF;cursor:auto;padding:10px 30px;"';
																						

	public function send() {

		$title = stripslashes($this->template['title']);
		$subtitle = stripslashes($this->template['subtitle']);
		$subject = stripslashes($this->template['subject']);
		if(empty($subject)) {
			$subject = $title;
		}
		if(empty($subtitle)) {
			$subtitle = $title;
		}
		$content = $this->template['content'];

		$deafult_searches = array(
			'[name]',
			'[i]',
			'[/i]',
			'[u]',
			'[/u]',
			'[b]',
			'[/b]',
			'[h1]',
			'[/h1]',
			'[h2]',
			'[/h2]',
			'[homepage]',
			'[/homepage]',
		);
		$deafult_replaces = array(
			$this->user->name,
			'<i>',
			'</i>',
			'<span style="text-decoration: underline;">',
			'</span>',
			'<b>',
			'</b>',
			'<div style="font-size: 18px; font-weight: bold;">',
			'</div>',
			'<div style="font-size: 15px; font-weight: bold;">',
			'</div>',
			'<a '.$this->button_style.' href="'.getLangUrl('/').'">',
			'</a>',
		);

		$title = str_replace($deafult_searches, $deafult_replaces, $title);
		$subtitle = str_replace($deafult_searches, $deafult_replaces, $subtitle);
		$subject = str_replace($deafult_searches, $deafult_replaces, $subject);
		$content = str_replace($deafult_searches, $deafult_replaces, $content);
		
		$content = $this->addPlaceholders($content);
		$title = $this->addPlaceholders($title);
		$subtitle = $this->addPlaceholders($subtitle);
		$subject = $this->addPlaceholders($subject);

		Mail::send('emails.template', [
				'user' => $this->user,
				'content' => $content,
				'title' => $title,
				'subtitle' => $subtitle,
			], function ($message) use ($subject) {

				$sender = config('mail.from.address');
				$sender_name = config('mail.from.name');

			    $message->from($sender, $sender_name);
			    $message->to( $this->user->email );
			    //$message->to( 'dokinator@gmail.com' );
				$message->replyTo($sender, $sender_name);
				$message->subject($subject);
        });

		$this->sent = 1;
		$this->save();
	}

	private function addPlaceholders($content) {

		if($this->template->id==1 || $this->template->id==2) { //Verify
			$content = str_replace(array(
				'[verifylink]',
				'[/verifylink]',
			), array(
				'<a '.$this->button_style.' href="'.getLangUrl('verify/'.$this->user->id.'/'.$this->user->get_token()).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==5) { //Recover
			$content = str_replace(array(
				'[recoverlink]',
				'[/recoverlink]',
			), array(
				'<a '.$this->button_style.' href="'.getLangUrl('recover/'.$this->user->id.'/'.$this->user->get_token()).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==6 || $this->template->id==8) { //New review & review reply
			$review = Review::find($this->meta['review_id']);


			$content = str_replace(array(
				'[author_name]',
				'[dentist_name]',
				'[rating]',
				'[reviewlink]',
				'[/reviewlink]',
			), array(
				$review->user->name,
				$review->dentist->name,
				$review->rating,
				'<a '.$this->button_style.' href="'.$review->dentist->getLink().'/'.$review->id.'">',
				'</a>',
			), $content);
		}

		if($this->template->id==7) { //Invite
			$content = str_replace(array(
				'[invitelink]',
				'[/invitelink]',
			), array(
				'<a '.$this->button_style.' href="'.getLangUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$this->meta['secret']).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==9) { //Add dentist
			$inviter = User::find($this->user->invited_by);
			$content = str_replace(array(
				'[inviter_name]',
				'[claimlink]',
				'[/claimlink]',
			), array(
				$inviter->getName(),
				'<a '.$this->button_style.' href="'.getLangUrl('claim/'.$this->user->id.'/'.$this->user->get_invite_token()).'">',
				'</a>',
			), $content);
		}

		return $content;
	}

    public function getMetaAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = json_encode($value);
    }

}
