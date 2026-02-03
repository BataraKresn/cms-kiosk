<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Middleware untuk log dan monitor query performance
 * Helps identify N+1 queries dan slow requests
 */
class QueryDebugMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Start query logging in development
        if (app()->environment('local', 'development')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        // Log query info if development
        if (app()->environment('local', 'development')) {
            $queries = DB::getQueryLog();
            $count = count($queries);
            $totalTime = collect($queries)
                ->sum('time');

            if ($count > 20 || $totalTime > 1000) {
                Log::warning('Slow request detected', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'queries' => $count,
                    'time_ms' => $totalTime,
                ]);
            }
        }

        return $response;
    }
}
