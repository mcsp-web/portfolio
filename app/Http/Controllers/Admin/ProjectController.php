<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\Screenshot;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::query()->ordered()->with(['screenshots', 'technologies'])->get();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects->map(fn (Project $p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'tagline' => $p->tagline,
                'categoryLabel' => Project::CATEGORIES[$p->category] ?? $p->category,
                'statusLabel' => Project::STATUSES[$p->status] ?? $p->status,
                'isPublished' => $p->is_published,
                'isFeatured' => $p->is_featured,
                'cover' => $p->cover()?->url(),
                'screenshotCount' => $p->screenshots->count(),
                'techCount' => $p->technologies->count(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Edit', $this->formProps(null));
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $project = Project::create([
            ...collect($data)->except('technology_ids')->all(),
            'sort_order' => (int) Project::max('sort_order') + 1,
        ]);
        $project->technologies()->sync($data['technology_ids'] ?? []);

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Project created. Add screenshots below.');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Edit', $this->formProps($project));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        $project->update(collect($data)->except('technology_ids')->all());
        $project->technologies()->sync($data['technology_ids'] ?? []);

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Changes saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        foreach ($project->screenshots as $shot) {
            if ($shot->disk !== 'bundled') {
                Storage::disk($shot->disk)->delete($shot->path);
            }
        }
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', "Deleted “{$project->title}”.");
    }

    /**
     * Flip the published or featured flag from the list view.
     */
    public function toggle(Request $request, Project $project): RedirectResponse
    {
        $field = $request->validate(['field' => ['required', 'in:is_published,is_featured']])['field'];
        $project->update([$field => ! $project->{$field}]);

        $label = $field === 'is_published'
            ? ($project->is_published ? 'now shown on the portfolio' : 'hidden from the portfolio')
            : ($project->is_featured ? 'featured' : 'no longer featured');

        return back()->with('success', "{$project->title} is {$label}.");
    }

    public function move(Request $request, Project $project): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        DB::transaction(function () use ($project, $direction) {
            $ids = Project::query()->ordered()->pluck('id')->values();
            $pos = $ids->search($project->id);
            $swap = $direction === 'up' ? $pos - 1 : $pos + 1;
            if ($swap < 0 || $swap >= $ids->count()) {
                return;
            }
            [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            foreach ($ids as $order => $id) {
                Project::whereKey($id)->update(['sort_order' => $order]);
            }
        });

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(?Project $project): array
    {
        $project?->load(['technologies', 'screenshots']);

        return [
            'project' => $project ? [
                ...$project->only([
                    'id', 'slug', 'title', 'tagline', 'category', 'status', 'client_label', 'role', 'year',
                    'summary', 'body', 'live_url', 'show_live_url', 'is_published', 'is_featured',
                ]),
                'highlights' => implode("\n", $project->highlights ?? []),
                'engineering' => implode("\n", $project->engineering ?? []),
                'technology_ids' => $project->technologies->pluck('id'),
                'screenshots' => $project->screenshots->map(fn (Screenshot $s) => $s->toPublic())->values(),
            ] : null,
            'categories' => Project::CATEGORIES,
            'statuses' => Project::STATUSES,
            'kinds' => Screenshot::KINDS,
            'layers' => Technology::LAYERS,
            'technologies' => Technology::query()->orderBy('layer')->orderBy('sort_order')->orderBy('name')
                ->get(['id', 'name', 'layer']),
        ];
    }
}
