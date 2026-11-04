<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Messages', [
            'messages' => Message::latest()->get()->map(fn (Message $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'company' => $m->company,
                'projectType' => Message::PROJECT_TYPES[$m->project_type] ?? null,
                'budget' => Message::BUDGETS[$m->budget] ?? null,
                'body' => $m->body,
                'emailed' => $m->emailed,
                'read' => $m->read_at !== null,
                'received' => $m->created_at->timezone('America/New_York')->format('M j, Y g:i A'),
            ]),
        ]);
    }

    public function toggleRead(Message $message): RedirectResponse
    {
        $message->update(['read_at' => $message->read_at ? null : now()]);

        return back();
    }

    public function destroy(Message $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Message deleted.');
    }
}
