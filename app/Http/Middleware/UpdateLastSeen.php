<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->user()) {
            $user = $request->user();
            // Update last_seen_at if it's null or older than 2 minutes
            if (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinutes(2))) {
                $user->timestamps = false; // Don't trigger updated_at
                $user->last_seen_at = now();
                $user->save();
            }
        }

        return $response;
    }
}
