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
            '[b]bold[/b]',
            '[i]italic[/i]',
            '[u]underline[/u]',
            '[h1]Heading 1 (18px)[/h1]',
            '[h2]Heading 2 (15px)[/h2]',
            '[homepage]Click here[/homepage]',
        ];

        if($this->id==1 || $this->id==2 || $this->id==11) {
            $codes[] = '[verifylink]Click here[/verifylink]';
        }

        if($this->id==3 || $this->id==4) {
            $codes[] = '[rewardlink]Click here[/rewardlink]';            
        }

        if($this->id==5 || $this->id==13) {
            $codes[] = '[recoverlink]Click here[/recoverlink]';
        } 

        if($this->id==6 || $this->id==8) {
            $codes[] = '[reviewlink]Click here[/reviewlink]';
            $codes[] = '[author_name]';
            $codes[] = '[dentist_name]';
            $codes[] = '[rating]';
        } 

        if($this->id==7) {
            $codes[] = '[invitelink]Click here[/invitelink]';
        } 

        if($this->id==9) {
            $codes[] = '[inviter_name]';
            $codes[] = '[claimlink]Click here[/claimlink]';
        } 
        
        if($this->id==15) {
            $codes[] = '[expires]';
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
