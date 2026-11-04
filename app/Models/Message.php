<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public const PROJECT_TYPES = [
        'web_app' => 'Web app or SaaS',
        'website' => 'Business website',
        'ai' => 'AI integration',
        'existing' => 'Work on an existing codebase',
        'other' => 'Something else',
    ];

    public const BUDGETS = [
        'under_1k' => 'Under $1,000',
        '1k_5k' => '$1,000 – $5,000',
        '5k_15k' => '$5,000 – $15,000',
        '15k_plus' => '$15,000+',
        'unsure' => 'Not sure yet',
    ];

    protected $fillable = ['name', 'email', 'company', 'project_type', 'budget', 'body', 'ip', 'emailed', 'read_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['emailed' => 'boolean', 'read_at' => 'datetime'];
    }
}
