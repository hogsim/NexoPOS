<?php

namespace Modules\NsCustomFields\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomFieldsAuth
{
    /**
     * Handle an incoming request - supports both session and token authentication
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via web guard (session)
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // Check if user is authenticated via sanctum (token)
        if (Auth::guard('sanctum')->check()) {
            return $next($request);
        }

        // Not authenticated via either method
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated.'
        ], 401);
    }
}
