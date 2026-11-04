<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\UpworkMode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Coolify's proxy (Traefik) and Cloudflare sit in front of the app and terminate HTTPS.
        // Trusting them lets Laravel see the real https:// address, so form posts, redirects and
        // flashed login errors work. Same setting as the other MCSP sites.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            UpworkMode::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An expired form (left open too long, or a stale tab) goes back to the page with a
        // message instead of a bare "Page expired" screen.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($response->getStatusCode() === 419 && ! $request->expectsJson()) {
                return back()->withErrors(['email' => 'This page expired. Please sign in again.']);
            }

            return $response;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
