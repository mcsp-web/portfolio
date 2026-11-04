<?php

namespace App\Support;

use App\Models\Project;

/**
 * Tags for link previews (LinkedIn, WhatsApp, Slack, X, iMessage, Upwork messages) and search
 * engines. They are written into the server-rendered HTML, because preview bots don't run JavaScript.
 */
class Meta
{
    /**
     * @param  array<string, string>  $profile
     * @return array<string, string>
     */
    public static function home(array $profile): array
    {
        return [
            'title' => "{$profile['name']}: full-stack and AI developer",
            'description' => $profile['intro'],
            'image' => self::image('home'),
            'type' => 'website',
            'site' => $profile['company'] ?: $profile['name'],
        ];
    }

    /**
     * @param  array<string, string>  $profile
     * @return array<string, string>
     */
    public static function project(Project $project, array $profile): array
    {
        $image = self::image($project->slug)
            ?? $project->cover()?->url()
            ?? self::image('home');

        return [
            'title' => "{$project->title}: {$project->tagline} | {$profile['name']}",
            'description' => (string) ($project->summary ?: $project->tagline),
            'image' => $image,
            'type' => 'article',
            'site' => $profile['company'] ?: $profile['name'],
        ];
    }

    /**
     * Designed preview card from public/images/og/<name>.jpg, if one exists.
     */
    private static function image(string $name): ?string
    {
        $file = "images/og/{$name}.jpg";

        return is_file(public_path($file))
            ? asset($file).'?v='.filemtime(public_path($file))
            : null;
    }
}
