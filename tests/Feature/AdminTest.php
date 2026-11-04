<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Screenshot;
use App\Models\Setting;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => bcrypt('correct-horse-battery')]);
    }

    private function project(): Project
    {
        return Project::create(['slug' => 'demo-app', 'title' => 'Demo App', 'category' => 'saas', 'status' => 'live']);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/projects', [])->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_in_and_wrong_password_is_rejected(): void
    {
        $this->admin();

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'correct-horse-battery'])->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_admin_creates_project_with_technologies_and_line_lists(): void
    {
        $tech = Technology::create(['name' => 'Vue 3', 'layer' => 'frontend']);

        $this->actingAs($this->admin())->post('/admin/projects', [
            'title' => 'New Shop', 'category' => 'client_site', 'status' => 'live',
            'highlights' => "Online orders\n\n  Card payments ", 'engineering' => '',
            'technology_ids' => [$tech->id], 'is_published' => true, 'is_featured' => false, 'show_live_url' => false,
        ])->assertRedirect();

        $project = Project::where('slug', 'new-shop')->firstOrFail();
        $this->assertSame(['Online orders', 'Card payments'], $project->highlights);
        $this->assertTrue($project->technologies->contains($tech));
    }

    public function test_toggle_hides_and_shows_a_project(): void
    {
        $project = $this->project();
        $this->actingAs($this->admin());

        $this->patch("/admin/projects/{$project->id}/toggle", ['field' => 'is_published']);
        $this->assertFalse($project->fresh()->is_published);
        $this->get('/work/demo-app')->assertNotFound();

        $this->patch("/admin/projects/{$project->id}/toggle", ['field' => 'is_published']);
        $this->assertTrue($project->fresh()->is_published);
    }

    public function test_toggle_rejects_other_fields(): void
    {
        $project = $this->project();
        $this->actingAs($this->admin())->patch("/admin/projects/{$project->id}/toggle", ['field' => 'title'])->assertSessionHasErrors('field');
    }

    public function test_reorder_swaps_neighbours(): void
    {
        $a = $this->project();
        $b = Project::create(['slug' => 'b', 'title' => 'B', 'category' => 'saas', 'status' => 'live', 'sort_order' => 1]);

        $this->actingAs($this->admin())->patch("/admin/projects/{$b->id}/move", ['direction' => 'up']);

        $this->assertSame(['b', 'demo-app'], Project::query()->ordered()->pluck('slug')->all());
    }

    public function test_screenshot_upload_cover_and_delete(): void
    {
        Storage::fake('public');
        $project = $this->project();
        $this->actingAs($this->admin());

        $this->post("/admin/projects/{$project->id}/screenshots", [
            'images' => [UploadedFile::fake()->image('a.png', 1200, 800), UploadedFile::fake()->image('b.png', 1200, 800)],
            'kind' => 'admin',
        ])->assertSessionHasNoErrors();

        $shots = $project->screenshots()->get();
        $this->assertCount(2, $shots);
        Storage::disk('public')->assertExists($shots[0]->path);

        $this->patch("/admin/screenshots/{$shots[1]->id}", ['caption' => 'Dashboard', 'kind' => 'admin', 'is_cover' => true]);
        $this->assertSame($shots[1]->id, $project->fresh()->cover()->id);

        $this->delete("/admin/screenshots/{$shots[0]->id}");
        Storage::disk('public')->assertMissing($shots[0]->path);
        $this->assertSame(1, Screenshot::count());
    }

    public function test_upload_rejects_non_images(): void
    {
        Storage::fake('public');
        $project = $this->project();

        $this->actingAs($this->admin())->post("/admin/projects/{$project->id}/screenshots", [
            'images' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')], 'kind' => 'public',
        ])->assertSessionHasErrors('images.0');
    }

    public function test_deleting_a_project_removes_uploaded_files(): void
    {
        Storage::fake('public');
        $project = $this->project();
        Storage::disk('public')->put('projects/demo-app/x.png', 'x');
        $project->screenshots()->create(['disk' => 'public', 'path' => 'projects/demo-app/x.png']);

        $this->actingAs($this->admin())->delete("/admin/projects/{$project->id}")->assertRedirect('/admin');

        Storage::disk('public')->assertMissing('projects/demo-app/x.png');
        $this->assertSame(0, Project::count());
    }

    public function test_profile_settings_save_and_reach_public_page(): void
    {
        $this->actingAs($this->admin())->put('/admin/profile', [
            'headline' => 'New headline', 'upwork_url' => 'https://www.upwork.com/freelancers/example',
        ] + Setting::DEFAULTS)->assertSessionHasNoErrors();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('profile.headline', 'New headline')
            ->where('profile.upwork_url', 'https://www.upwork.com/freelancers/example'));
    }

    public function test_technology_crud(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/stack', ['name' => 'Rust', 'layer' => 'backend', 'show_on_profile' => true])->assertSessionHasNoErrors();
        $tech = Technology::where('name', 'Rust')->firstOrFail();
        $this->post('/admin/stack', ['name' => 'Rust', 'layer' => 'backend'])->assertSessionHasErrors('name');

        $this->put("/admin/stack/{$tech->id}", ['name' => 'Rust', 'layer' => 'infra', 'show_on_profile' => false]);
        $this->assertSame('infra', $tech->fresh()->layer);

        $this->delete("/admin/stack/{$tech->id}");
        $this->assertSame(0, Technology::count());
    }

    public function test_make_admin_command(): void
    {
        $this->artisan('portfolio:admin', ['email' => 'me@example.test'])
            ->expectsQuestion('Password (min 12 characters)', 'short')
            ->assertFailed();

        $this->artisan('portfolio:admin', ['email' => 'me@example.test'])
            ->expectsQuestion('Password (min 12 characters)', 'long-enough-password')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'me@example.test']);
    }

    public function test_wrong_password_returns_a_visible_error_to_the_login_page(): void
    {
        $this->admin();

        $this->from('/admin/login')
            ->followingRedirects()
            ->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'wrong'])
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Login')
                ->where('errors.email', 'Wrong email or password. Check both and try again.'));
        $this->assertGuest();
    }

    public function test_repeated_wrong_passwords_lock_out_with_a_message(): void
    {
        $this->admin();
        foreach (range(1, 5) as $i) {
            $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'correct-horse-battery'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString('Too many sign-in attempts', session('errors')->first('email'));
    }

    public function test_requests_through_the_https_proxy_generate_https_urls(): void
    {
        $this->get('/admin/login', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'portfolio.example.com'])
            ->assertOk()
            ->assertSee('https://portfolio.example.com', false);
    }

    public function test_expired_form_goes_back_with_a_message(): void
    {
        $this->withMiddleware(ValidateCsrfToken::class);
        $this->app->instance('env', 'local');

        $response = $this->from('/admin/login')->post('/admin/login', ['email' => 'a@example.test', 'password' => 'x', '_token' => 'stale']);

        $response->assertRedirect('/admin/login');
    }
}
