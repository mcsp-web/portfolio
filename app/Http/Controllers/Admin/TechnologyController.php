<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TechnologyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Stack', [
            'layers' => Technology::LAYERS,
            'technologies' => Technology::query()
                ->withCount('projects')
                ->orderBy('sort_order')->orderBy('name')
                ->get()
                ->map(fn (Technology $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'layer' => $t->layer,
                    'show_on_profile' => $t->show_on_profile,
                    'projects' => $t->projects_count,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Technology::create($data + ['sort_order' => (int) Technology::max('sort_order') + 1]);

        return back()->with('success', "Added {$data['name']}.");
    }

    public function update(Request $request, Technology $technology): RedirectResponse
    {
        $technology->update($this->validated($request, $technology));

        return back()->with('success', "Saved {$technology->name}.");
    }

    public function destroy(Technology $technology): RedirectResponse
    {
        $technology->delete();

        return back()->with('success', "Removed {$technology->name}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Technology $technology = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('technologies', 'name')->ignore($technology?->id)],
            'layer' => ['required', Rule::in(array_keys(Technology::LAYERS))],
            'show_on_profile' => ['boolean'],
        ]);
    }
}
