<?php

namespace App\Http\Controllers;

use App\Http\Middleware\UpworkMode;
use App\Mail\NewMessage;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Upwork and Fiverr visitors must stay on the marketplace, so the form is closed to them.
        abort_if(UpworkMode::active($request), 404);

        // Bots fill the hidden field; pretend it worked and keep nothing.
        if (filled($request->input('website'))) {
            return back()->with('contact_sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'project_type' => ['nullable', Rule::in(array_keys(Message::PROJECT_TYPES))],
            'budget' => ['nullable', Rule::in(array_keys(Message::BUDGETS))],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
        ], [
            'name.required' => 'Enter your name.',
            'email.required' => 'Enter your email so I can reply.',
            'email.email' => 'Enter a valid email address.',
            'body.required' => 'Tell me a little about the project.',
            'body.min' => 'Add a bit more detail (at least 20 characters).',
        ]);

        $key = 'contact|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'body' => 'You’ve sent several messages already. Please try again in an hour.',
            ]);
        }
        RateLimiter::hit($key, 3600);

        $message = Message::create($data + ['ip' => $request->ip()]);

        // Saved first, emailed second: a mail failure never loses the message.
        $to = Setting::allValues()['email'] ?? null;
        if ($to) {
            try {
                Mail::to($to)->send(new NewMessage($message));
                $message->update(['emailed' => true]);
            } catch (\Throwable $e) {
                Log::warning('Portfolio enquiry saved but not emailed', ['id' => $message->id, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('contact_sent', true);
    }
}
