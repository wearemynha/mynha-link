<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Headers
{
    public function handle(Request $request, Closure $next)
    {
        // URL generation may be forced for deployments that explicitly opt in.
        if (config('linkstack.force_https')) {
            \URL::forceScheme('https'); // Force HTTPS
            header("Content-Security-Policy: upgrade-insecure-requests");
        }

        if (config('linkstack.force_route_https') && !$request->isSecure()) {
            $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url");
            exit();
        }

        return $next($request);
    }
}
