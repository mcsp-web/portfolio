<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_preview_tags_in_the_server_html(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="James McCalla: full-stack and AI developer">', $html);
        $this->assertMatchesRegularExpression('#<meta property="og:image" content="[^"]+/images/og/home\.jpg\?v=\d+">#', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.url('/').'">', $html);
    }

    public function test_project_page_uses_its_own_card_and_summary(): void
    {
        Project::create(['slug' => 'pureops', 'title' => 'PureOps', 'tagline' => 'Field-service management',
            'summary' => 'Runs a service business.', 'category' => 'saas', 'status' => 'live']);

        $html = $this->get('/work/pureops?via=upwork')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:description" content="Runs a service business.">', $html);
        $this->assertMatchesRegularExpression('#og:image" content="[^"]+/images/og/pureops\.jpg#', $html);
        // The shared URL never carries the marketplace switch.
        $this->assertStringContainsString('<meta property="og:url" content="'.url('/work/pureops').'">', $html);
    }

    public function test_project_without_a_card_falls_back_to_the_home_card(): void
    {
        Project::create(['slug' => 'new-thing', 'title' => 'New thing', 'tagline' => 'X', 'category' => 'saas', 'status' => 'live']);

        $this->assertMatchesRegularExpression('#og:image" content="[^"]+/images/og/home\.jpg#', $this->get('/work/new-thing')->getContent());
    }

    public function test_sitemap_lists_only_published_pages(): void
    {
        Project::create(['slug' => 'shown', 'title' => 'Shown', 'category' => 'saas', 'status' => 'live']);
        Project::create(['slug' => 'hidden', 'title' => 'Hidden', 'category' => 'saas', 'status' => 'live', 'is_published' => false]);

        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<loc>'.url('/work/shown').'</loc>', false)
            ->assertDontSee('hidden');
    }

    public function test_robots_blocks_admin_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: '.url('/sitemap.xml'));
        $this->get('/admin/login')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
