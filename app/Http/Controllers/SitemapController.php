<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $projects = Project::query()->published()->ordered()->get(['slug', 'updated_at']);
        $latest = $projects->max('updated_at') ?? now();

        $urls = collect([['loc' => route('home'), 'lastmod' => $latest]])
            ->concat($projects->map(fn (Project $p) => ['loc' => route('projects.show', $p->slug), 'lastmod' => $p->updated_at]));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .$urls->map(fn ($u) => '  <url><loc>'.e($u['loc']).'</loc><lastmod>'.$u['lastmod']->toDateString().'</lastmod></url>')->implode("\n")
            ."\n</urlset>\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
