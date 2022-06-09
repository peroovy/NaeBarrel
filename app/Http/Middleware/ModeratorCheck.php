<?php

namespace App\Http\Middleware;

use App\Enums\Permissions;
use Closure;
use Illuminate\Http\Request;

class ModeratorCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = \Auth::user();
        if (!$user || ($user->permission != Permissions::Moderator && $user->permission != Permissions::Admin)) {
            return response(status: 403);
        }
        return $next($request);
    }
}
