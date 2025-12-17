<?php

namespace Kashifleo\MultiDBBridge\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kashifleo\MultiDBBridge\Facades\DbBridge;
use Symfony\Component\HttpFoundation\Response;

class EnsureDbBridgeConnected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!DbBridge::isConnected()) {
            abort(403, 'Unauthorized: No tenant connected.');
        }

        return $next($request);
    }
}
