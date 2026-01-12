<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek environment variable
        if (env('APP_MAINTENANCE_MODE', false) === 'true' || env('APP_MAINTENANCE_MODE', false) === true) {
            
            // Allow bypass dengan secret token
            $secret = env('MAINTENANCE_SECRET', 'recovery2026');
            
            // Cek cookie bypass
            if ($request->cookie('laravel_maintenance') === $secret) {
                return $next($request);
            }
            
            // Cek URL bypass
            if ($request->is($secret)) {
                // Set cookie untuk bypass
                return redirect('/')->cookie('laravel_maintenance', $secret, 60 * 24 * 7); // 7 days
            }
            
            // Tampilkan maintenance page
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}
