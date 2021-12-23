<?php

namespace Souravmsh\PasswordPolicy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Souravmsh\PasswordPolicy\Http\Traits\PasswordPolicy;

class PasswordExpiryCheck
{
    use PasswordPolicy;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    { 
        $response = $next($request);

        if (config('password-policy.enable') && $request->user())
        {
            $isExpired = $this->passwordLoginIsExpired(); 
            if ($isExpired && !in_array($request->route()->getName(), $this->passwordLoginIsExpiredRouteAllowed))
            {
                return redirect($isExpired);
            }
        }

        return $response;
    }
}
