<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    /** Layers, in the order they appear on the public stack section. */
    public const LAYERS = [
        'frontend' => 'Front end',
        'backend' => 'Back end',
        'data' => 'Databases & storage',
        'ai' => 'AI & machine learning',
        'infra' => 'Infrastructure & DevOps',
        'integration' => 'APIs & integrations',
        'testing' => 'Testing & quality',
    ];

    protected $fillable = ['name', 'layer', 'show_on_profile', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['show_on_profile' => 'boolean'];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }
}
