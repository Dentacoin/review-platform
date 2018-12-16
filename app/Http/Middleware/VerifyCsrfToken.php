<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'test',
        'location',
        'user-name',
        'suggest-clinic',
        'suggest-clinic/*',
        'suggest-dentist',
        'suggest-dentist/*',
        'wait',
        'civic',
        'mobident',
        'cms/*',
        '*/dental-survey-stats/*',
        '*/register/upload',
        '*/registration/upload',
        '*/questionnaire/*',
        '*/paid-dental-surveys/*',
        '*/profile/avatar',
        '*/youtube',
        '*/profile/jwt',
        '*/profile/info/upload',
        '*/profile/gallery',
        '*/profile/clinics/*',
        '*/profile/dentists/*',
    ];
}
