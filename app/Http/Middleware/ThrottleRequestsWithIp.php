<?php

namespace App\Http\Middleware;

use App\Models\AdminIp;
use Closure;

class ThrottleRequestsWithIp extends \GrahamCampbell\Throttle\Http\Middleware\ThrottleMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '') {
        
        if(in_array($request->ip(), AdminIp::getAdminIps())) {
            return $next($request);
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }
}