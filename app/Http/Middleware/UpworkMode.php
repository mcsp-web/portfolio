<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Upwork's and Fiverr's terms forbid sharing contact details before a contract starts, including on
 * a website linked from a profile. Visiting with ?via=upwork (or ?via=fiverr) switches this session to
 * a version of the site with every email, phone and off-platform link removed;
 * ?via=direct switches it back.
 */
class UpworkMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $via = $request->query('via');
        if (in_array($via, ['upwork', 'fiverr', 'marketplace'], true)) {
            $request->session()->put('upwork_mode', $via === 'fiverr' ? 'fiverr' : 'upwork');
        } elseif ($via === 'direct') {
            $request->session()->forget('upwork_mode');
        }

        return $next($request);
    }

    public static function active(Request $request): bool
    {
        return (bool) $request->session()->get('upwork_mode', false);
    }

    /** Which marketplace the visitor came from: "upwork", "fiverr" or null. */
    public static function marketplace(Request $request): ?string
    {
        $mode = $request->session()->get('upwork_mode');

        return is_string($mode) ? $mode : null;
    }
}
