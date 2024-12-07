<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCreator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the authenticated user has the 'creator' role
        if (auth()->check() && auth()->user()->role === 'creator') {
            return $next($request);
        }

        // Deny access if the user is not a creator
        return response()->json([
            'success' => false,
            'message' => 'Access denied. Only creators are allowed to access this resource.',
        ], 403);
    }
}
