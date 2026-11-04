<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($lines) => collect(is_array($lines) ? $lines : preg_split('/\r?\n/', (string) $lines))
            ->map(fn ($l) => trim((string) $l))
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'highlights' => $clean($this->input('highlights')),
            'engineering' => $clean($this->input('engineering')),
            'slug' => str((string) ($this->input('slug') ?: $this->input('title')))->slug()->toString(),
            'stats' => collect(preg_split('/\r?\n/', (string) $this->input('stats')))
                ->map(fn ($line) => array_map('trim', explode('|', $line, 2)))
                ->filter(fn ($parts) => count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '')
                ->map(fn ($parts) => ['value' => $parts[0], 'label' => $parts[1]])
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'tagline' => ['nullable', 'string', 'max:200'],
            'category' => ['required', Rule::in(array_keys(Project::CATEGORIES))],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'client_label' => ['nullable', 'string', 'max:160'],
            'role' => ['nullable', 'string', 'max:160'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'summary' => ['nullable', 'string', 'max:600'],
            'body' => ['nullable', 'string', 'max:10000'],
            'highlights' => ['array', 'max:20'],
            'highlights.*' => ['string', 'max:300'],
            'engineering' => ['array', 'max:20'],
            'engineering.*' => ['string', 'max:300'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'show_live_url' => ['boolean'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'show_feature_list' => ['boolean'],
            'stats' => ['array', 'max:6'],
            'stats.*.value' => ['string', 'max:20'],
            'stats.*.label' => ['string', 'max:60'],
            'technology_ids' => ['array'],
            'technology_ids.*' => ['integer', 'exists:technologies,id'],
        ];
    }
}
