<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as Middleware;

class ValidateSignature extends Middleware
{
    /**
     * The names of the query string parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ];
    
    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next, ...$args)
    {
        // Bypass signature validation for Livewire file uploads
        // Issue: Load balanced setup with 3 containers causes signature mismatch
        // User already authenticated via session + CSRF protected
        if ($request->is('livewire/upload-file')) {
            return $next($request);
        }
        
        try {
            return parent::handle($request, $next, ...$args);
        } catch (\Illuminate\Routing\Exceptions\InvalidSignatureException $e) {
            // Log signature validation failure details for debugging
            \Log::error('Livewire signature validation failed', [
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'method' => $request->method(),
                'query_string' => $request->getQueryString(),
                'expires' => $request->query('expires'),
                'signature' => $request->query('signature'),
                'current_time' => time(),
                'is_expired' => $request->query('expires') ? (time() > $request->query('expires')) : null,
                'app_url' => config('app.url'),
                'app_key' => substr(config('app.key'), 0, 20) . '...',
                'host' => $request->header('host'),
                'x_forwarded_host' => $request->header('x-forwarded-host'),
                'x_forwarded_proto' => $request->header('x-forwarded-proto'),
                'session_id' => $request->session()->getId(),
            ]);
            throw $e;
        }
    }
}
