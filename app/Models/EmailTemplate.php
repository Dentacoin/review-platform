<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes;
    use \Dimsav\Translatable\Translatable;

    public $translatedAttributes = [
    	"content",
        "title",
    	"subtitle",
        "subject",
    ];

    protected $fillable = [
    	"content",
        "title",
        "subtitle",
        "subject",
    	"name",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at', 
        'deleted_at'
    ];

    public function shortcodes() {
        $codes = [
            '[name]',
            '[platform]',
            '[b]bold[/b]',
            '[i]italic[/i]',
            '[u]underline[/u]',
            '[h1]Heading 1 (18px)[/h1]',
            '[h2]Heading 2 (15px)[/h2]',
            '[homepage]Click here[/homepage]',
            '[metamask] Instructions [/metamask]',
        ];

        if($this->id==4) {
            $codes[] = '[register_reward]';
            $codes[] = '[rewardlink]Click here[/rewardlink]';            
        }

        if($this->id==13) {
            $codes[] = '[recoverlink]Click here[/recoverlink]';
        } 

        if($this->id==6 || $this->id==8 || $this->id==21) {
            $codes[] = '[reviewlink]Click here[/reviewlink]';
            $codes[] = '[author_name]';
            $codes[] = '[dentist_name]';
            $codes[] = '[rating]';
        } 

        if($this->id==1) {
            $codes[] = '[clinic_name]';
            $codes[] = '[invitelink]Click here[/invitelink]';

        }
        if($this->id==7 || $this->id==17 || $this->id==25 || $this->id==27) {
            $codes[] = '[friend_name]';
            $codes[] = '[invitelink]Click here[/invitelink]';
        } 

        if($this->id==10) {
            $codes[] = '[link]Click here[/link]';
        }
        
        if($this->id==15) {
            $codes[] = '[expires]';
            $codes[] = '[ban_hours]';
            $codes[] = '[ban_days]';
        } 

        
        if($this->id==14 || $this->id==26 || $this->id==40) {
            $codes[] = '[welcome_link]Click here[/welcome_link]';
            $codes[] = '[become_dcn_dentist]Click here[/become_dcn_dentist]';
        } 

        if($this->id==18 || $this->id==19 || $this->id==22) {
            $codes[] = '[who_joined_name]';
        } 
        
        if($this->id==20) {
            $codes[] = '[transaction_amount]';
            $codes[] = '[transaction_address]';
            $codes[] = '[transaction_link]Click here[/transaction_link]';
        } 

        if($this->id==23) {
            $codes[] = '[patient_name]';
            $codes[] = '[invitation_link]Click here[/invitation_link]';
        } 
        if($this->id==24) {
            $codes[] = '[dentist_name]';
            $codes[] = '[dentist_link]Click here[/dentist_link]';
        } 
        if($this->id==28) {
            $codes[] = '[reward]Claim your reward[/reward]';
        } 
        if($this->id==31 || $this->id==32) {
            $codes[] = '[agree] agree button text [/agree]';
            $codes[] = '[privacy] privacy link text [/privacy]';
        } 

        if($this->id==33) {
            $codes[] = '[clinic-name]';
            $codes[] = '[profile-link]Click here[/profile-link]';
        }

        if($this->id==34 || $this->id==2) {
            $codes[] = '[dentist-name]';
            $codes[] = '[profile-link]Click here[/profile-link]';
        }

        if($this->id==35 || $this->id==36 || $this->id==37) {
            $codes[] = '[clinic-name]';
        }

        if($this->id==38) {
            $codes[] = '[dentist-name]';
        }

        if($this->id==11 || $this->id==39) {
            $codes[] = '[grace_expiration_date]';
            $codes[] = '[login]Click here[/login]';
        }

        if( $this->id==3 || $this->id==5 || $this->id==41 ) { //Unfinished registrations
            $codes[] = '[button]Click here to finish the registration[/button]';
            $codes[] = '[missing-info]';
        }

        return $codes;
    }
}

class EmailTemplateTranslation extends Model {

	public $timestamps = false;
	protected $fillable = [
        "content",
        "title",
        "subtitle",
        "subject",
	];

}
