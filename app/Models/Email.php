<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use \SendGrid\Mail\PlainTextContent as PlainTextContent;
use \SendGrid\Mail\HtmlContent as HtmlContent;
use \SendGrid\Mail\Mail as SendGridMail;
use \SendGrid\Mail\Subject as Subject;
use \SendGrid\Mail\From as From;
use \SendGrid\Mail\To as To;

use App\Helpers\GeneralHelper;

use App\Models\Review;
use App\Models\Reward;
use App\Models\User;

class Email extends Model {
	
    use SoftDeletes;

    protected $fillable = [
    	"user_id",
    	"template_id",
    	"meta",
    	"platform",
    	"sent",
    	"invalid_email",
    	"unsubscribed",
	];

	public static $template_types = [
		'trp', 
		'vox', 
		'common', 
		'assurance', 
		'dentacare', 
		'dentacoin', 
		'dentists', 
		'support',
	];

	public function template() {
        return $this->hasOne('App\Models\EmailTemplate', 'id', 'template_id')->withTrashed();		
	}

	public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();		
	}

	private $button_style = 'style="text-decoration: none;background: #0084d1;font-family: Ubuntu,Helvetica,Arial,sans-serif;font-size: 15px;font-weight: normal;line-height: 120%;text-transform: none;margin: 0px;border: none;border-radius: 25px;color: #ffffff;padding: 10px 30px;"';
	private $text_style = 'style="text-decoration:underline; color: #38a2e5;"';																			

	public function send($anonymous_email=null) {

		if(!$this->user || (!$this->user->email && !$this->user->mainBranchEmail())) {
			return;
		}

		$user_email = $this->user->email ? $this->user->email : $this->user->mainBranchEmail();

        if(empty($anonymous_email)) {
	        if($this->user->id != 3 && !empty($this->template->subscribe_category)) {
	            $cat = $this->template->subscribe_category;
	            if($this->platform != 'dentacare' && $this->platform != 'dentists' && !in_array($this->platform, $this->user->$cat)) {
	                $this->unsubscribed = true;
	                $this->save();
	            }
	        }
	    }

        if (empty($this->unsubscribed) && filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
			list($content, $title, $subtitle, $subject) = $this->prepareContent();

			$platform = $this->template_id==20 ? 'dentacoin' : $this->platform;
			$sender = $platform=='vox' ? config('mail.from.address-vox') : config('mail.from.address-dentacoin');
			if($this->template_id==40 || $this->template_id==14) {
				$sender = 'business@dentacoin.com';
			}
			$sender_name = $platform=='vox' ? config('mail.from.name-vox') : config('mail.from.name-dentacoin');
			
			$contents = view('emails.template', [
				'user' => $this->user,
				'content' => $content,
				'title' => $title,
				'subtitle' => $subtitle,
				'platform' => $platform,
				'unsubscribe' => 'https://api.dentacoin.com/api/update-single-email-preference/'.'?'. http_build_query([
					'fields' => urlencode(GeneralHelper::encrypt(json_encode([
						'email' => ($anonymous_email ? $anonymous_email : $user_email),
						'email_category' => $this->template->subscribe_category, 
						'platform' => $this->platform 
					])))
				]),
			])->render();

	        $from = new From($sender, $sender_name);
	        $tos = [new To( $user_email)];

	        $email = new SendGridMail(
	            $from,
	            $tos
	        );
	        
	        if ($this->template->category) {
	        	$email->addCategory($this->template->category);
	        } else {
	        	$email->addCategory(strtoupper($platform).' Service '.($this->user->is_dentist ? 'Dentist' : 'Patient'));
	        }
	        $email->setSubject($subject);
	        $email->setReplyTo($sender, $sender_name);
			$email->addContent(
			    "text/html", $contents
			);
	        
	        $sendgrid = new \SendGrid(env('SENDGRID_PASSWORD'));
	        $sendgrid->send($email);

			$this->sent = 1;
		} else {
			$this->sent = 0;
		}

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
			'[invite-patients-button]',
			'[/invite-patients-button]',
		);
		$deafult_replaces = array(
			$this->user->getNames(),
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
			'</a>',
			'<a '.$this->button_style.' href="'.getLangUrl( 'dentist/'.$this->user->slug, null, $domain).'?'. http_build_query(['popup'=>'popup-invite']).'">',
			'</a>',
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

		if(in_array($this->template->id, [6, 8, 21])) { //New review & review reply
			$review = Review::find($this->meta['review_id']);

			if($review) {
				$dentist_id = $this->meta['dentist_id'];
				$dentist = User::find($dentist_id);

				$content = str_replace(array(
					'[author_name]',
					'[dentist_name]',
					'[rating]',
					'[reviewlink]',
					'[/reviewlink]',
				), array(
					$review->user->name,
					$dentist->name,
					!empty($review->team_doctor_rating) && ($dentist->id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating,
					'<a '.$this->button_style.' href="'.$dentist->getLink().'/?review_id='.$review->id.'">',
					'</a>',
				), $content);
			}
		}

		if($this->template->id==15) { //Ban
			$content = str_replace('[expires]', $this->meta['expires'], $content);
			$content = str_replace('[ban_days]', $this->meta['ban_days'], $content);
			$content = str_replace('[ban_hours]', $this->meta['ban_hours'], $content);
		}

		if($this->template->id==22) { //Invitation accepted
			$content = str_replace(array(
				'[who_joined_name]',
			), array(
				$this->meta['who_joined_name'] ?? '',
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

		if($this->template->id==23) { //user asks Dentist
			$content = str_replace(array(
				'[patient_name]',
				'[invitation_link]',
				'[/invitation_link]',
			), array(
				$this->meta['patient_name'],
				'<a '.$this->button_style.' href="'.$this->meta['invitation_link'].'">',
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
			if(!empty( $this->meta['profile-link'] )) {
				$content = str_replace(array(
					'[dentist-name]',
					'[profile-link]',
					'[/profile-link]',
				), array(
					$this->meta['dentist-name'],
					'<a '.$this->button_style.' href="'.$this->meta['profile-link'].'">',
					'</a>',
				), $content);
			} else {
				$content = str_replace(array(
					'[dentist-name]',
					'[profile-link]',
					'[/profile-link]',
				), array(
					$this->meta['dentist-name'],
					'<a '.$this->button_style.' href="'.$this->user->getLink().'">',
					'</a>',
				), $content);
			}
		}

		if($this->template->id==35 || $this->template->id==36) { //Clinic Accepts Dentist Request
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

		if($this->template->id==63) { //user asks Dentist
			$content = str_replace(array(
				'[patient_name]',
				'[invitation_link]',
				'[/invitation_link]',
			), array(
				$this->meta['patient_name'],
				'<a '.$this->button_style.' href="'.getLangUrl( '/' , null, $domain).'?'. http_build_query(['dcn-gateway-type'=>'dentist-login']).'">',
				'</a>',				
			), $content);
		}

		if($this->template->id==64) { //Dentist Approves Review Verification Request
			$content = str_replace(array(
				'[dentist_name]',
				'[rewardlink]',
				'[/rewardlink]',
			), array(
				$this->meta['dentist_name'],
				'<a '.$this->button_style.' href="'.getLangUrl( 'profile/home' , null, $domain).'">',
				'</a>',
			), $content);
		}

		if($this->template->id==109 || $this->template->id==110 ) { // Patient Status from Deleted to Suspicious
			$content = str_replace(array(
				'[login-button]',
				'[/login-button]',
			), array(
				'<a '.$this->button_style.' href="https://dentacoin.com/?dcn-gateway-type=patient-login">',
				'</a>',
			), $content);
		}

		if($this->template->id==111 || $this->template->id==112 ) { // Patient Status from Deleted to Verified
			$content = str_replace(array(
				'[login-button]',
				'[/login-button]',
				'[faq-link]',
				'[/faq-link]',
			), array(
				'<a '.$this->button_style.' href="https://dentacoin.com/?dcn-gateway-type=patient-login">',
				'</a>',
				'<a '.$this->text_style.' href="https://dentavox.dentacoin.com/en/faq/">',
				'</a>',
			), $content);
		}

		return $content;
	}

    public function getMetaAttribute($value) {
        return $value ? json_decode($value, true) : [];
    }

    public function setMetaAttribute($value) {
        $this->attributes['meta'] = json_encode($value);
    }
}
