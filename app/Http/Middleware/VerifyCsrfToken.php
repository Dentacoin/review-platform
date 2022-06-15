<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier {
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'test',
        'location',
        'search-dentists',
        'dentist-location',
        'dentist-city',
        'suggest-clinic',
        'suggest-clinic/*',
        'suggest-dentist',
        'suggest-dentist/*',
        'civic',
        'get-popup',
        'cms/*',
        '*/dental-survey-stats/*',
        '*/register/upload',
        '*/youtube',
        '*/profile/info/upload',
        '*/profile/clinics/*',
        '*/profile/dentists/*',
        '*/login',
        '*/get-poll-stats/*',
        '*/get-poll-content/*',
        '*/daily-polls/*',
        '*/get-polls/*',
        '*/facebook-tab*',
        '*/download-statistics/',
        '*/create-stat-pdf/',
        '*/create-stat-png/',
        '*/paid-dental-surveys/*',
        '*/start-over',
        '*/profile/invite',
        '*/index-down',
        '*/index-dentist-down',
        '*/dentist-down',
        '*/vox-public-down',
        '*/voxes-get',
        '*/voxes-sort',
        '*/api/*',
        '*/authenticate-user',
    ];
}
