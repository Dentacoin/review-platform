<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Review;
use App\Models\Reward;
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
    	"platform",
	];


	public static $template_types = [
		'trp', 'vox', 'common'
	];

	public function template() {
        return $this->hasOne('App\Models\EmailTemplate', 'id', 'template_id')->withTrashed();		
	}

	public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();		
	}

	private $button_style = 'style="text-decoration:none;background:#126585;color:#FFFFFF;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;text-transform:none;margin:0px;border:none;border-radius:5px;color:#FFFFFF;cursor:auto;padding:10px 30px;"';
	private $text_style = 'style="text-decoration:underline; color: #38a2e5;"';
																						

	public function send() {

		if(!$this->user || !$this->user->email) {
			return;
		}

		list($content, $title, $subtitle, $subject) = $this->prepareContent();

		$platform = $this->platform;
		$sender = $platform=='vox' ? config('mail.from.address-vox') : config('mail.from.address');
		if($this->template_id==40) {
			$sender = 'ali.hashem@dentacoin.com';
		}
		$sender_name = $platform=='vox' ? config('mail.from.name-vox') : config('mail.from.name');
		Mail::send('emails.template', [
				'user' => $this->user,
				'content' => $content,
				'title' => $title,
				'subtitle' => $subtitle,
				'platform' => $platform,
			], function ($message) use ($subject, $platform, $sender, $sender_name) {
			    $message->from($sender, $sender_name);
			    $message->to( $this->user->email );
			    //$message->to( 'dokinator@gmail.com' );
				$message->replyTo($sender, $sender_name);
				$message->subject($subject);
        });

		$this->sent = 1;
		$this->save();
	}

	public function prepareContent() {

		$title = stripslashes($this->template['title']);
		$subtitle = stripslashes($this->template['subtitle']);
		$subject = stripslashes($this->template['subject']);
		if(empty($subject)) {
			$subject = $title;
		}
		$content = $this->template['content'];
		$domain = 'https://'.config('platforms.'.$this->platform.'.url').'/';

		$deafult_searches = array(
			'[name]',
			'[platform]',
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
			'[metamask]',
			'[/metamask]',
		);
		$deafult_replaces = array(
			$this->user->getName(),
			config('platforms.'.$this->platform.'.name'),
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
			'<a '.$this->button_style.' href="'.getLangUrl('/', null, $domain).'">',
			'</a>',
			'<a href="'.url($this->platform=='vox' ? 'DentavoxMetamask.pdf' : 'MetaMaskInstructions.pdf').'">',
			'</a>'
		);

		$title = str_replace($deafult_searches, $deafult_replaces, $title);
		$subtitle = str_replace($deafult_searches, $deafult_replaces, $subtitle);
		$subject = str_replace($deafult_searches, $deafult_replaces, $subject);
		$content = str_replace($deafult_searches, $deafult_replaces, $content);
		
		$content = $this->addPlaceholders($content, $domain);
		$title = $this->addPlaceholders($title, $domain);
		$subtitle = $this->addPlaceholders($subtitle, $domain);
		$subject = $this->addPlaceholders($subject, $domain);

		return [$content, $title, $subtitle, $subject];
	}

	private function addPlaceholders($content, $domain) {

		if($this->template->id==4) { //Reward
			$content = str_replace(array(
				'[register_reward]',
				'[rewardlink]',
				'[/rewardlink]',
			), array(
				Reward::getReward('reward_register'),
				'<a '.$this->button_style.' href="'.getLangUrl('profile/reward', null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==10) { //Share
			$content = str_replace(array(
				'[link]',
				'[/link]',
			), array(
				'<a '.$this->button_style.' href="'.$this->meta['link'].'">',
				'</a>',
			), $content);
		}

		if($this->template->id==13) { //Recover
			$content = str_replace(array(
				'[recoverlink]',
				'[/recoverlink]',
			), array(
				'<a '.$this->button_style.' href="'.getLangUrl('recover/'.$this->user->id.'/'.$this->user->get_token(), null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==6 || $this->template->id==8 || $this->template->id==21) { //New review & review reply
			$review = Review::find($this->meta['review_id']);

			if($review) {
				$dentist_or_clinic = $review->dentist_id ? $review->dentist : $review->clinic;

				$content = str_replace(array(
					'[author_name]',
					'[dentist_name]',
					'[rating]',
					'[reviewlink]',
					'[/reviewlink]',
				), array(
					$review->user->name,
					$dentist_or_clinic->name,
					$review->rating,
					'<a '.$this->button_style.' href="'.$dentist_or_clinic->getLink().'/'.$review->id.'">',
					'</a>',
				), $content);
			}
		}

		if($this->template->id==1 ) { //Invite
			$content = str_replace(array(
				'[clinic_name]',
				'[invitelink]',
				'[/invitelink]',
			), array(
				$this->meta['clinic_name'] ?? '',
				'<a '.$this->button_style.' href="'.getLangUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$this->meta['invitation_id'], null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==7 || $this->template->id==17 || $this->template->id==25 || $this->template->id==27) { //Invite
			$content = str_replace(array(
				'[friend_name]',
				'[invitelink]',
				'[/invitelink]',
			), array(
				$this->meta['friend_name'],
				'<a '.$this->button_style.' href="'.getLangUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$this->meta['invitation_id'], null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==15) { //Ban
			$content = str_replace('[expires]', $this->meta['expires'], $content);
			$content = str_replace('[ban_days]', $this->meta['ban_days'], $content);
			$content = str_replace('[ban_hours]', $this->meta['ban_hours'], $content);
		}

		if($this->template->id==18 || $this->template->id==19 || $this->template->id==22 || $this->template->id==27) { //Invitation accepted
			$content = str_replace(array(
				'[who_joined_name]',
			), array(
				$this->meta['who_joined_name']
			), $content);
		}

		if($this->template->id==20) { //Transaction completed
			$content = str_replace(array(
				'[transaction_amount]',
				'[transaction_address]',
				'[transaction_link]',
				'[/transaction_link]',
			), array(
				$this->meta['transaction_amount'],
				$this->meta['transaction_address'],
				'<a href="'.$this->meta['transaction_link'].'" target="_blank">',
				'</a>',
			), $content);
		}

		if($this->template->id==26 || $this->template->id==40 || $this->template->id==14) { //Dentist approved
			$content = str_replace(array(
				'[welcome_link]',
				'[/welcome_link]',
				'[become_dcn_dentist]',
				'[/become_dcn_dentist]',
			), array(
				'<a '.$this->text_style.' href="'.getLangUrl('welcome-to-dentavox', null, $domain).'">',
				'</a>',		
				'<a '.$this->button_style.' target="_blank" href="https://dentists.dentacoin.com/#contacts">',
				'</a>',				
			), $content);
		}

		if($this->template->id==23) { //user asks Dentist
			$content = str_replace(array(
				'[patient_name]',
				'[invitation_link]',
				'[/invitation_link]',
			), array(
				$this->meta['patient_name'],
				'<a '.$this->button_style.' href="'.getLangUrl('profile/asks', null, $domain).'">',
				'</a>',				
			), $content);
		}

		if($this->template->id==24) { //Dentist approves user request
			$content = str_replace(array(
				'[dentist_name]',
				'[dentist_link]',
				'[/dentist_link]',
			), array(
				$this->meta['dentist_name'],
				'<a '.$this->button_style.' href="'.$this->meta['dentist_link'].'">',
				'</a>',
			), $content);
		}

		if($this->template->id==28) { //Civic
			$content = str_replace(array(
				'[reward]',
				'[/reward]',
			), array(
				'<a '.$this->button_style.' href="'.getLangUrl('profile/wallet', null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==31 || $this->template->id==32) { //Transaction completed
			$content = str_replace(array(
				'[agree]',
				'[/agree]',
				'[privacy]',
				'[/privacy]',
			), array(
				'<a '.$this->button_style.' href="https://dentacoin.com/privacy-policy" target="_blank">',
				'</a>',
				'<a href="https://dentacoin.com/privacy/" target="_blank">',
				'</a>',
			), $content);
		}


		if($this->template->id==33) { //Invite Dentist to Join Clinic
			$content = str_replace(array(
				'[clinic-name]',
				'[profile-link]',
				'[/profile-link]',
			), array(
				$this->meta['clinic-name'] ?? '',
				!empty($this->meta['clinic-link']) ? '<a '.$this->button_style.' href="'.$this->meta['clinic-link'].'">' : '',
				!empty($this->meta['clinic-link']) ? '</a>' : '',
			), $content);
		}


		if($this->template->id==34) { //Dentist Wants to Join Clinic
			$content = str_replace(array(
				'[dentist-name]',
				'[profile-link]',
				'[/profile-link]',
			), array(
				$this->meta['dentist-name'],
				'<a '.$this->button_style.' href="'.$this->user->getLink().'?tab=about">',
				'</a>',
			), $content);
		}

		if($this->template->id==34 || $this->template->id==2) { //Dentist Wants to Join Clinic
			if(!empty( $this->meta['profile-link'] )) {
				$content = str_replace(array(
					'[dentist-name]',
					'[profile-link]',
					'[/profile-link]',
				), array(
					$this->meta['dentist-name'],
					'<a '.$this->button_style.' href="'.$this->meta['profile-link'].'?tab=about">',
					'</a>',
				), $content);
			}
		}


		if($this->template->id==35 || $this->template->id==36 || $this->template->id==37) { //Clinic Accepts Dentist Request
			$content = str_replace(array(
				'[clinic-name]',
			), array(
				$this->meta['clinic-name'] ?? '-',
			), $content);
		}

		if($this->template->id==38) { //Dentist Leaves Clinic
			$content = str_replace(array(
				'[dentist-name]',
			), array(
				$this->meta['dentist-name'],
			), $content);
		}



		if($this->template->id==11 || $this->template->id==39) { //New Auth
			$content = str_replace(array(
				'[grace_expiration_date]',
				'[login]',
				'[/login]',
			), array(
				date('d F Y', time()+86400*7),
				'<a '.$this->text_style.' href="'.getLangUrl( ( $this->template->id==11 ? 'login' : '/' ) , null, $domain).'">',
				'</a>',
			), $content);
		}

		if( $this->template->id==3 || $this->template->id==5 || $this->template->id==41 ) { //Unfinished registrations
			$content = str_replace(array(
				'[button]',
				'[/button]',
				'[missing-info]',
				'[unsubscribe]',
				'[/unsubscribe]',
			), array(
				'<a '.$this->button_style.' href="'.getLangUrl( 'welcome-dentist/'.$this->meta['link'], null, $domain).'">',
				'</a>',
				!empty($this->meta['missing-info']) ? $this->meta['missing-info'] : '',
				'<a '.$this->text_style.' href="'.getLangUrl( 'welcome-dentist/unsubscribe/'.$this->meta['link'], null, $domain).'">',
				'</a>',
			), $content);
        }

		if($this->template->id==42 ) { // Invite Clinic After Dentist Registration
			$content = str_replace(array(
				'[dentist-name]',
				'[button]',
				'[/button]',
			), array(
				$this->meta['dentist_name'],
				'<a '.$this->button_style.' href="'.getLangUrl( 'welcome-dentist' , null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==43 ) { // Patient Invites Dentist To Register
			$content = str_replace(array(
				'[patient-name]',
				'[dentist-name]',
				'[button]',
				'[/button]',
			), array(
				$this->meta['patient_name'],
				$this->meta['dentist_name'],
				'<a '.$this->button_style.' href="'.getLangUrl( 'welcome-dentist/claim/'.$this->user_id.'/'.$this->user->get_invite_token() , null, $domain).'?'. http_build_query(['popup'=>'claim-popup']).'">',
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
