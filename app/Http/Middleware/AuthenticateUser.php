<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateUser
{

    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('login');
                
            }
        }

        return $next($request);
    }
}
