<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnonymousUser extends Model {
    
    protected $fillable = [        
        'email',
        'website_notifications',
        'unsubscribed_website_notifications',
        'blog',
        'unsubscribed_blog',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function setWebsiteNotificationsAttribute($value) {
        $this->attributes['website_notifications'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['website_notifications'] = implode(',', $value);            
        }
    }
    
    public function getWebsiteNotificationsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setBlogAttribute($value) {
        $this->attributes['blog'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['blog'] = implode(',', $value);            
        }
    }
    
    public function getBlogAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setUnsubscribedWebsiteNotificationsAttribute($value) {
        $this->attributes['unsubscribed_website_notifications'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['unsubscribed_website_notifications'] = implode(',', $value);            
        }
    }
    
    public function getUnsubscribedWebsiteNotificationsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }

    public function setUnsubscribedBlogAttribute($value) {
        $this->attributes['unsubscribed_blog'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['unsubscribed_blog'] = implode(',', $value);            
        }
    }
    
    public function getUnsubscribedBlogAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
}

?>