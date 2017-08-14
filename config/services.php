<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    
    'facebook' => [
        'client_id' => '1906201509652855',
        'client_secret' => 'd3be0739681b061dcab618623d9853dd',
        'redirect' => ('login/callback/facebook'),
    ],
    
    'twitter' => [
        'client_id' => 'qtsd7dH0IOO6hv77XlVswPQWx',
        'client_secret' => 'hQ1JcZBciOdOSWFaCKaL8zyxA2HFkA8UJ7QlmcPIEWQ5Xo7ESi',
        'redirect' => ('login/callback/twitter'),
    ],
    
    'google' => [
        'client_id' => '23312461529-il5nho35bplnu3huuife61jl4t8cciv4.apps.googleusercontent.com',
        'client_secret' => 'WLdK71fPK3b5ICuAGPUnnEAG',
        'redirect' => ('login/callback/gplus'),
    ],


];
