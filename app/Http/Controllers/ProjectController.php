<?php

namespace App\Http\Controllers;

use App\Http\Middleware\UpworkMode;
use App\Models\Project;
use App\Models\Screenshot;
use App\Models\Setting;
use App\Models\Technology;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $project->load(['technologies', 'screenshots', 'features']);

        $ordered = Project::query()->published()->ordered()->get(['id', 'slug', 'title']);
        $index = $ordered->search(fn (Project $p) => $p->id === $project->id);
        $next = $ordered->get(($index + 1) % max($ordered->count(), 1));

        return Inertia::render('Project', [
            'profile' => Setting::publicValues(UpworkMode::active(request())),
            'project' => [
                ...$project->toCard(),
                'clientLabel' => $project->client_label,
                'role' => $project->role,
                'body' => $project->body,
                'highlights' => $project->highlights ?? [],
                'stats' => $project->stats ?? [],
                'featureGroups' => $project->publicFeatureGroups(),
                'engineering' => $project->engineering ?? [],
                'liveUrl' => $project->show_live_url && ! UpworkMode::active(request()) ? $project->live_url : null,
                'stack' => collect(Technology::LAYERS)
                    ->map(fn ($label, $key) => [
                        'label' => $label,
                        'items' => $project->technologies->where('layer', $key)->pluck('name')->values(),
                    ])
                    ->filter(fn ($layer) => $layer['items']->isNotEmpty())
                    ->values(),
                'screenshots' => $project->shownScreenshots()->map(fn (Screenshot $s) => $s->toPublic())->values(),
            ],
            'next' => $next && $next->id !== $project->id ? ['slug' => $next->slug, 'title' => $next->title] : null,
        ]);
    }
}
