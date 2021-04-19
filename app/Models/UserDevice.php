<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model {
        
    protected $fillable = [
        'user_id',
        'device_id',
        'device_token',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
    
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public static function sendPush($title, $message, $meta = null) {
        
        $data = [
            "registration_ids" => self::whereNotNull('device_token')->get()->pluck('device_token')->toArray(),
            "content_available" => true,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ],
        ];
        if(!empty($meta)) {
            $data['data'] = $meta;
        }
        $dataString = json_encode($data);
  
        $headers = [
            'Authorization: key='.env('FIREBASE_KEY'),
            'Content-Type: application/json',
        ];
  
        $ch = curl_init();
  
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        
        curl_exec($ch);
    }
}

?>