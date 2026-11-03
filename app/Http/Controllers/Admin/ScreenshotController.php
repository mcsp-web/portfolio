<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Screenshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ScreenshotController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'images' => ['required', 'array', 'max:12'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:6144'],
            'kind' => ['required', Rule::in(array_keys(Screenshot::KINDS))],
        ]);

        $disk = config('portfolio.upload_disk');
        $order = (int) $project->screenshots()->max('sort_order');

        foreach ($data['images'] as $file) {
            $path = $file->store("projects/{$project->slug}", ['disk' => $disk, 'visibility' => 'public']);
            $project->screenshots()->create([
                'disk' => $disk,
                'path' => $path,
                'kind' => $data['kind'],
                'caption' => null,
                'sort_order' => ++$order,
            ]);
        }

        $count = count($data['images']);

        return back()->with('success', $count === 1 ? 'Screenshot uploaded.' : "{$count} screenshots uploaded.");
    }

    public function update(Request $request, Screenshot $screenshot): RedirectResponse
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:200'],
            'kind' => ['required', Rule::in(array_keys(Screenshot::KINDS))],
            'is_cover' => ['boolean'],
        ]);

        DB::transaction(function () use ($screenshot, $data) {
            if (! empty($data['is_cover'])) {
                Screenshot::where('project_id', $screenshot->project_id)->update(['is_cover' => false]);
            }
            $screenshot->update($data);
        });

        return back()->with('success', 'Screenshot updated.');
    }

    public function move(Request $request, Screenshot $screenshot): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        DB::transaction(function () use ($screenshot, $direction) {
            $ids = Screenshot::where('project_id', $screenshot->project_id)
                ->orderBy('sort_order')->orderBy('id')->pluck('id')->values();
            $pos = $ids->search($screenshot->id);
            $swap = $direction === 'up' ? $pos - 1 : $pos + 1;
            if ($swap < 0 || $swap >= $ids->count()) {
                return;
            }
            [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            foreach ($ids as $order => $id) {
                Screenshot::whereKey($id)->update(['sort_order' => $order]);
            }
        });

        return back();
    }

    public function destroy(Screenshot $screenshot): RedirectResponse
    {
        if ($screenshot->disk !== 'bundled') {
            Storage::disk($screenshot->disk)->delete($screenshot->path);
        }
        $screenshot->delete();

        return back()->with('success', 'Screenshot removed.');
    }
}
