<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Cookie;

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
        'dentist-location',
        'suggest-clinic',
        'suggest-clinic/*',
        'suggest-dentist',
        'suggest-dentist/*',
        'wait',
        'civic',
        'mobident',
        'get-popup',
        'cms/*',
        '*/dental-survey-stats/*',
        '*/register/upload',
        '*/youtube',
        '*/profile/info/upload',
        '*/profile/gallery',
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
        // '*/voxes-get',
    ];


    // protected function addCookieToResponse($request, $response) {
    //     $config = config('session');
 
    //     $response->headers->setCookie(
    //         new Cookie(
    //             'XSRF-TOKEN', $request->session()->token(), Carbon::now()->getTimestamp() + 60 * $config['lifetime'],
    //             $config['path'], $config['domain'], $config['secure'], true
    //         )
    //     );
 
    //     return $response;
    // }
}
