<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceLivewireJs200
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Force 200 for Livewire JS files
        if ($request->is('livewire/livewire.js*') && $response->getStatusCode() === 404) {
            $response->setStatusCode(200);
        }
        
        return $response;
    }
}
