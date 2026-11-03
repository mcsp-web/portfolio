<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Upwork's terms forbid sharing contact details before a contract starts, including on a
 * website linked from a profile. Visiting with ?via=upwork switches this browser session to
 * a version of the site with every email, phone and off-platform link removed;
 * ?via=direct switches it back.
 */
class UpworkMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $via = $request->query('via');
        if ($via === 'upwork') {
            $request->session()->put('upwork_mode', true);
        } elseif ($via === 'direct') {
            $request->session()->forget('upwork_mode');
        }

        return $next($request);
    }

    public static function active(Request $request): bool
    {
        return (bool) $request->session()->get('upwork_mode', false);
    }
}
