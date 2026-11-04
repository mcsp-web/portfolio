<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeatureController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:160'],
            'detail' => ['nullable', 'string', 'max:300'],
        ]);

        $project->features()->create($data + [
            'sort_order' => (int) $project->features()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Feature added.');
    }

    public function update(Request $request, Feature $feature): RedirectResponse
    {
        $data = $request->validate([
            'group_name' => ['sometimes', 'required', 'string', 'max:80'],
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'detail' => ['sometimes', 'nullable', 'string', 'max:300'],
            'is_visible' => ['sometimes', 'boolean'],
        ]);

        $feature->update($data);

        return back();
    }

    public function destroy(Feature $feature): RedirectResponse
    {
        $feature->delete();

        return back()->with('success', 'Feature removed.');
    }

    /**
     * Show, hide or rename a whole group at once.
     */
    public function group(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', 'string', 'max:80'],
            'is_visible' => ['sometimes', 'boolean'],
            'rename' => ['sometimes', 'required', 'string', 'max:80'],
        ]);

        $query = $project->features()->where('group_name', $data['group']);

        if (array_key_exists('is_visible', $data)) {
            $query->update(['is_visible' => $data['is_visible']]);
            $message = $data['is_visible'] ? "Showing “{$data['group']}”." : "Hid “{$data['group']}”.";
        } else {
            $query->update(['group_name' => $data['rename']]);
            $message = 'Group renamed.';
        }

        return back()->with('success', $message);
    }

    /**
     * Move a whole group up or down; features keep their order inside it.
     */
    public function moveGroup(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', 'string'],
            'direction' => ['required', 'in:up,down'],
        ]);

        DB::transaction(function () use ($project, $data) {
            $grouped = $project->features()->get()->groupBy('group_name');
            $names = $grouped->keys()->values();
            $pos = $names->search($data['group']);
            if ($pos === false) {
                return;
            }
            $swap = $data['direction'] === 'up' ? $pos - 1 : $pos + 1;
            if ($swap < 0 || $swap >= $names->count()) {
                return;
            }
            [$names[$pos], $names[$swap]] = [$names[$swap], $names[$pos]];

            $order = 0;
            foreach ($names as $name) {
                foreach ($grouped[$name] as $feature) {
                    $feature->update(['sort_order' => $order++]);
                }
            }
        });

        return back();
    }
}
