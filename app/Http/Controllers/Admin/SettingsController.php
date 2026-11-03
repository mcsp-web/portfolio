<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Profile', ['settings' => Setting::allValues()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'company' => ['nullable', 'string', 'max:80'],
            'headline' => ['required', 'string', 'max:140'],
            'intro' => ['required', 'string', 'max:500'],
            'about' => ['nullable', 'string', 'max:3000'],
            'availability' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120'],
            'upwork_url' => ['nullable', 'url', 'max:255'],
            'fiverr_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
        ]);

        Setting::put($data);

        return back()->with('success', 'Profile saved.');
    }
}
