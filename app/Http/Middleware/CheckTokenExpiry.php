<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if ($bearer) {
            $token = PersonalAccessToken::findToken($bearer);

            if ($token && $token->hasAttribute('expires_at')) {
                if (Carbon::now()->greaterThan($token->getAttributeValue('expires_at'))) {
                    return response()->json(['message' => 'Token has expired.'], 401);
                }
            }
        }

        return $next($request);
    }
}
