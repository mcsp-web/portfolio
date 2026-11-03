<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** Categories shown as filters on the public site. */
    public const CATEGORIES = [
        'saas' => 'SaaS & web apps',
        'ai' => 'AI & automation',
        'client_site' => 'Client websites',
        'tool' => 'Internal tools',
    ];

    public const STATUSES = [
        'live' => 'Live',
        'in_development' => 'In development',
        'demo' => 'Demo',
        'internal' => 'Internal tool',
    ];

    protected $fillable = [
        'slug', 'title', 'tagline', 'category', 'status', 'client_label', 'role', 'year',
        'summary', 'body', 'highlights', 'engineering', 'live_url', 'show_live_url',
        'is_published', 'is_featured', 'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'engineering' => 'array',
            'show_live_url' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)->orderBy('sort_order')->orderBy('name');
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function cover(): ?Screenshot
    {
        $shots = $this->relationLoaded('screenshots') ? $this->screenshots : $this->screenshots()->get();

        return $shots->firstWhere('is_cover', true) ?? $shots->first();
    }

    /**
     * Shape used by public cards and lists.
     *
     * @return array<string, mixed>
     */
    public function toCard(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'tagline' => $this->tagline,
            'category' => $this->category,
            'categoryLabel' => self::CATEGORIES[$this->category] ?? $this->category,
            'status' => $this->status,
            'statusLabel' => self::STATUSES[$this->status] ?? $this->status,
            'year' => $this->year,
            'summary' => $this->summary,
            'isFeatured' => $this->is_featured,
            'cover' => $this->cover()?->toPublic(),
            'technologies' => $this->technologies->pluck('name')->all(),
            'screenshotCount' => $this->screenshots->count(),
        ];
    }
}
