<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Screenshot extends Model
{
    /** What part of the product the image shows. */
    public const KINDS = [
        'public' => 'Public site',
        'admin' => 'Back office',
        'mobile' => 'Mobile',
        'cli' => 'Terminal / tooling',
    ];

    protected $fillable = ['project_id', 'disk', 'path', 'caption', 'kind', 'is_cover', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * A bundled image whose file was removed from the repo is skipped, never shown broken.
     */
    public function fileExists(): bool
    {
        return $this->disk !== 'bundled' || is_file(public_path('images/projects/'.$this->path));
    }

    /**
     * Bundled images ship in public/images; uploads live on a storage disk.
     */
    public function url(): string
    {
        if ($this->disk === 'bundled') {
            return asset('images/projects/'.$this->path);
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublic(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'caption' => $this->caption,
            'kind' => $this->kind,
            'kindLabel' => self::KINDS[$this->kind] ?? $this->kind,
            'isCover' => $this->is_cover,
        ];
    }
}
