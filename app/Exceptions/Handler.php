<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

use Exception;
use Request;
use Log;

class Handler extends ExceptionHandler {
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \League\OAuth2\Server\Exception\OAuthServerException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception) {
        
        if( app()->runningInConsole() || $exception instanceof \Illuminate\Session\TokenMismatchException || in_array(get_class($exception), ['Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'League\OAuth2\Server\Exception\OAuthServerException', 'Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException']) ) {
            ;
        } else {
            if(Request::url() != 'https://dentavox.dentacoin.com/get-unseen-notifications-count' && Request::url() != 'https://reviews.dentacoin.com/get-unseen-notifications-count' ) {

                Log::error("URL: " . Request::url().' - Method: '.Request::method());
                Log::error("GET: " . json_encode($_GET));
                Log::error("POST: " . json_encode($_POST));
                Log::error("EXCEPTION TYPE: " . get_class($exception));
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception) {
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        session([
            'our-intended' => $request->url()
        ]);

        return redirect( getLangUrl('login') );
    }
}
