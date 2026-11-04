<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Editable profile fields and their defaults. */
    public const DEFAULTS = [
        'name' => 'James McCalla',
        'company' => 'MCSP Consulting',
        'headline' => 'I build the software small businesses run on.',
        'intro' => 'Full-stack developer working end to end: database, back end, front end, AI and deployment. I take a product from a blank repo to a live, tested, monitored system.',
        'about' => '',
        'availability' => 'Taking new projects',
        'location' => 'Miami, FL and remote',
        'email' => 'info@mcspconsulting.com',
        'upwork_url' => 'https://www.upwork.com/freelancers/~01294f831c6e9e058c',
        'fiverr_url' => '',
        'linkedin_url' => 'https://www.linkedin.com/in/christopher-mccalla-923702170',
        'github_url' => '',
        'website_url' => 'https://mcspconsulting.com',
    ];

    /**
     * @return array<string, string>
     */
    public static function allValues(): array
    {
        return Cache::rememberForever('settings.all', function (): array {
            $stored = static::query()->pluck('value', 'key')->all();

            return array_map(fn ($v) => (string) $v, array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null)));
        });
    }

    /** Fields that would let a visitor contact me off-platform. */
    public const CONTACT_FIELDS = ['email', 'linkedin_url', 'github_url', 'website_url'];

    /**
     * Profile values for public pages. In Upwork mode contact details are removed
     * server-side, so they never reach the page data at all.
     *
     * @return array<string, string>
     */
    public static function publicValues(bool $upworkMode): array
    {
        $values = static::allValues();
        if ($upworkMode) {
            foreach (self::CONTACT_FIELDS as $field) {
                $values[$field] = '';
            }
        }

        return $values;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                static::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
            }
        }
        Cache::forget('settings.all');
    }
}
