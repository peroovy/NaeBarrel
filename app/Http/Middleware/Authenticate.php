<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try
        {
            $this->authenticate($request, $guards);

            return $next($request);
        }
        catch (AuthenticationException $exception)
        {
            return response(status: 401);
        }
    }
}
