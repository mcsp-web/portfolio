<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FeatureListTest extends TestCase
{
    use RefreshDatabase;

    private function project(array $attrs = []): Project
    {
        return Project::create($attrs + ['slug' => 'app', 'title' => 'App', 'category' => 'saas', 'status' => 'live']);
    }

    private function admin(): User
    {
        return User::create(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('long-enough-password')]);
    }

    private function seedFeatures(Project $p): void
    {
        foreach ([['Billing', 'Invoices'], ['Billing', 'Refunds'], ['Scheduling', 'Drag and drop']] as $i => [$g, $n]) {
            $p->features()->create(['group_name' => $g, 'name' => $n, 'sort_order' => $i]);
        }
    }

    public function test_project_page_groups_visible_features_and_shows_stats(): void
    {
        $p = $this->project(['stats' => [['value' => '1,300+', 'label' => 'automated tests']]]);
        $this->seedFeatures($p);
        Feature::where('name', 'Refunds')->update(['is_visible' => false]);

        $this->get('/work/app')->assertInertia(fn (Assert $page) => $page
            ->has('project.featureGroups', 2)
            ->where('project.featureGroups.0.name', 'Billing')
            ->has('project.featureGroups.0.features', 1)
            ->where('project.stats.0.value', '1,300+'));
    }

    public function test_switching_off_the_list_hides_everything(): void
    {
        $p = $this->project(['show_feature_list' => false]);
        $this->seedFeatures($p);

        $this->get('/work/app')->assertInertia(fn (Assert $page) => $page->has('project.featureGroups', 0));
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('projects.0.featureCount', 0));
    }

    public function test_admin_hides_and_shows_a_whole_group(): void
    {
        $p = $this->project();
        $this->seedFeatures($p);
        $this->actingAs($this->admin());

        $this->patch("/admin/projects/{$p->id}/feature-groups", ['group' => 'Billing', 'is_visible' => false]);
        $this->assertSame(0, $p->features()->where('group_name', 'Billing')->where('is_visible', true)->count());
        $this->assertSame(1, $p->features()->where('group_name', 'Scheduling')->where('is_visible', true)->count());

        $this->patch("/admin/projects/{$p->id}/feature-groups", ['group' => 'Billing', 'is_visible' => true]);
        $this->assertSame(2, $p->features()->where('group_name', 'Billing')->where('is_visible', true)->count());
    }

    public function test_admin_adds_edits_hides_and_removes_a_feature(): void
    {
        $p = $this->project();
        $this->actingAs($this->admin());

        $this->post("/admin/projects/{$p->id}/features", ['group_name' => 'Reports', 'name' => 'Margin report'])->assertSessionHasNoErrors();
        $f = $p->features()->firstOrFail();

        $this->patch("/admin/features/{$f->id}", ['is_visible' => false]);
        $this->assertFalse($f->fresh()->is_visible);

        $this->patch("/admin/features/{$f->id}", ['name' => 'Profit report', 'detail' => 'Per job']);
        $this->assertSame('Profit report', $f->fresh()->name);

        $this->delete("/admin/features/{$f->id}");
        $this->assertSame(0, Feature::count());
    }

    public function test_admin_renames_and_reorders_groups(): void
    {
        $p = $this->project();
        $this->seedFeatures($p);
        $this->actingAs($this->admin());

        $this->patch("/admin/projects/{$p->id}/feature-groups/move", ['group' => 'Scheduling', 'direction' => 'up']);
        $this->assertSame('Scheduling', $p->features()->first()->group_name);

        $this->patch("/admin/projects/{$p->id}/feature-groups", ['group' => 'Billing', 'rename' => 'Billing and payments']);
        $this->assertSame(2, $p->features()->where('group_name', 'Billing and payments')->count());
    }

    public function test_guests_cannot_change_features(): void
    {
        $p = $this->project();
        $this->post("/admin/projects/{$p->id}/features", ['group_name' => 'X', 'name' => 'Y'])->assertRedirect('/admin/login');
        $this->assertSame(0, Feature::count());
    }

    public function test_stats_are_parsed_from_lines_in_the_project_form(): void
    {
        $p = $this->project();
        $this->actingAs($this->admin())->put("/admin/projects/{$p->id}", [
            'title' => 'App', 'category' => 'saas', 'status' => 'live',
            'stats' => "1,300+ | automated tests\nbad line\n7 | industries",
            'is_published' => true, 'is_featured' => false, 'show_live_url' => false, 'show_feature_list' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame([['value' => '1,300+', 'label' => 'automated tests'], ['value' => '7', 'label' => 'industries']], $p->fresh()->stats);
    }

    public function test_seeder_loads_features_once_and_keeps_admin_edits(): void
    {
        $this->seed(PortfolioSeeder::class);
        $askdoc = Project::where('slug', 'askdoc')->firstOrFail();
        $this->assertGreaterThan(0, $askdoc->features()->count());
        $this->assertNotEmpty($askdoc->stats);

        $askdoc->features()->first()->update(['is_visible' => false, 'name' => 'Edited']);
        $count = $askdoc->features()->count();

        $this->seed(PortfolioSeeder::class);
        $this->assertSame($count, $askdoc->features()->count());
        $this->assertTrue($askdoc->features()->where('name', 'Edited')->where('is_visible', false)->exists());
    }
}
