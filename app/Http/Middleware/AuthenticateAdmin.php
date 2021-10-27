<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

use Closure;

class AuthenticateAdmin {

    public function handle($request, Closure $next) {

        if (Auth::guard('admin')->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('cms/login');
            }
        }

        return $next($request);
    }
}
