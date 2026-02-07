<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block requests with suspicious user agents (common scrapers/bots)
        $blockedAgents = [
            'curl',
            'wget',
            'python-requests',
            'scrapy',
            'bot',
            'crawler',
            'spider',
        ];

        $userAgent = strtolower($request->userAgent() ?? '');

        foreach ($blockedAgents as $agent) {
            if (str_contains($userAgent, $agent)) {
                return response()->json([
                    'error' => 'Unauthorized client'
                ], 403);
            }
        }

        // Require Accept: application/json header
        if (!$request->expectsJson()) {
            return response()->json([
                'error' => 'Invalid Accept header. Must accept application/json'
            ], 406);
        }

        return $next($request);
    }
}
