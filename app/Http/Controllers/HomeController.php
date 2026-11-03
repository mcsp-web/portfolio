<?php

namespace App\Http\Controllers;

use App\Http\Middleware\UpworkMode;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Technology;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $projects = Project::query()
            ->published()
            ->ordered()
            ->with(['technologies', 'screenshots'])
            ->get();

        $publishedIds = $projects->pluck('id');

        $stack = Technology::query()
            ->where('show_on_profile', true)
            ->withCount(['projects' => fn ($q) => $q->whereIn('projects.id', $publishedIds)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('layer');

        $layers = collect(Technology::LAYERS)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'items' => ($stack[$key] ?? collect())
                    ->sortBy([['projects_count', 'desc'], ['sort_order', 'asc']])
                    ->map(fn (Technology $t) => [
                        'name' => $t->name,
                        'projects' => $t->projects_count,
                    ])->values(),
            ])
            ->filter(fn (array $layer) => $layer['items']->isNotEmpty())
            ->values();

        return Inertia::render('Home', [
            'profile' => Setting::publicValues(UpworkMode::active(request())),
            'projects' => $projects->map->toCard()->values(),
            'categories' => collect(Project::CATEGORIES)
                ->filter(fn ($label, $key) => $projects->contains('category', $key))
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
                ->values(),
            'stack' => $layers,
        ]);
    }
}
