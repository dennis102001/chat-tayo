<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SlideTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $response = $next($request);

        $token = $request->user()?->currentAccessToken();

        if(!$token || !$token->expires_at){
            return $response;
        }

        if($token->expires_at->lessThan(now()->addHour())){
            $token->update([
                'expires_at' => $token->expires_at->copy()->addHours(2)
            ]);
        }

        return $response;
    }
}
