<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Setting;
use App\Models\Technology;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPortfolioTest extends TestCase
{
    use RefreshDatabase;

    private function project(array $attrs = []): Project
    {
        return Project::create($attrs + [
            'slug' => 'demo-app', 'title' => 'Demo App', 'category' => 'saas', 'status' => 'live', 'is_published' => true,
        ]);
    }

    public function test_home_lists_only_published_projects(): void
    {
        $this->project();
        $this->project(['slug' => 'secret', 'title' => 'Secret App', 'is_published' => false]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('projects', 1)
            ->where('projects.0.slug', 'demo-app'));
    }

    public function test_stack_counts_only_published_projects(): void
    {
        $tech = Technology::create(['name' => 'Laravel', 'layer' => 'backend']);
        $this->project()->technologies()->attach($tech);
        $this->project(['slug' => 'hidden', 'title' => 'Hidden', 'is_published' => false])->technologies()->attach($tech);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('stack.0.key', 'backend')
            ->where('stack.0.items.0.projects', 1));
    }

    public function test_project_page_shows_a_published_project(): void
    {
        $this->project(['summary' => 'Hello']);

        $this->get('/work/demo-app')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Project')
            ->where('project.title', 'Demo App')
            ->where('project.liveUrl', null));
    }

    public function test_hidden_project_page_is_not_found(): void
    {
        $this->project(['is_published' => false]);

        $this->get('/work/demo-app')->assertNotFound();
    }

    public function test_live_url_only_shown_when_allowed(): void
    {
        $this->project(['live_url' => 'https://example.com', 'show_live_url' => true]);

        $this->get('/work/demo-app')->assertInertia(fn (Assert $page) => $page->where('project.liveUrl', 'https://example.com'));
    }

    public function test_seeder_runs_and_is_safe_to_rerun(): void
    {
        $this->seed(PortfolioSeeder::class);
        $count = Project::count();
        Project::where('slug', 'pureops')->update(['title' => 'Edited in admin']);

        $this->seed(PortfolioSeeder::class);

        $this->assertSame($count, Project::count());
        $this->assertSame('Edited in admin', Project::where('slug', 'pureops')->value('title'));
        $this->get('/')->assertOk();
    }

    public function test_upwork_mode_strips_contact_details_for_the_session(): void
    {
        $this->project(['live_url' => 'https://example.com', 'show_live_url' => true]);
        Setting::put(['email' => 'me@example.test', 'upwork_url' => 'https://www.upwork.com/freelancers/x', 'linkedin_url' => 'https://linkedin.com/in/x']);

        $this->get('/?via=upwork')->assertInertia(fn (Assert $page) => $page
            ->where('upworkMode', true)
            ->where('profile.email', '')
            ->where('profile.linkedin_url', '')
            ->where('profile.upwork_url', 'https://www.upwork.com/freelancers/x'));

        // Sticks for the rest of the visit, including project pages.
        $this->get('/work/demo-app')->assertInertia(fn (Assert $page) => $page
            ->where('profile.email', '')
            ->where('project.liveUrl', null));

        $this->get('/?via=direct')->assertInertia(fn (Assert $page) => $page->where('profile.email', 'me@example.test'));
    }
}
