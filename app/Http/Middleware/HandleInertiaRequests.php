<?php

namespace App\Http\Middleware;

use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => ['user' => $request->user()?->only(['name', 'email'])],
            'upworkMode' => fn () => UpworkMode::active($request),
            'marketplace' => fn () => UpworkMode::marketplace($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'contactSent' => fn () => (bool) $request->session()->get('contact_sent'),
            ],
            'unreadMessages' => fn () => $request->user() ? Message::whereNull('read_at')->count() : 0,
        ];
    }
}
