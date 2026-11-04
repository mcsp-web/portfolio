<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AskDocEditionsTest extends TestCase
{
    use RefreshDatabase;

    private function runRenameMigration(): void
    {
        $migration = require database_path('migrations/2026_11_04_000400_rename_askdoc_to_askdoc_rag.php');
        $migration->up();
    }

    public function test_seeder_adds_askdoc_lite_with_features_screenshots_and_hidden_live_link(): void
    {
        $this->seed(PortfolioSeeder::class);

        $lite = Project::where('slug', 'askdoc-lite')->firstOrFail();
        $this->assertTrue($lite->is_published);
        $this->assertFalse($lite->show_live_url);
        $this->assertGreaterThan(0, $lite->features()->count());
        $this->assertSame(3, $lite->screenshots()->count());
        $this->assertTrue($lite->technologies()->where('name', 'Claude citations & prompt caching')->exists());

        $this->get('/work/askdoc-lite')->assertOk()->assertSee('AskDoc Lite')
            ->assertDontSee('aidemo.mcspconsulting.com');
    }

    public function test_rename_migration_updates_untouched_askdoc_fields_only(): void
    {
        $original = [
            'slug' => 'askdoc', 'category' => 'ai', 'status' => 'demo',
            'tagline' => 'Ask questions of any document and get answers with page citations',
            'body' => "Intro.\n\nThe live demo is paused to save server resources. These screenshots use a sample employee handbook.",
        ];

        $untouched = Project::create($original + ['title' => 'AskDoc']);
        $this->runRenameMigration();
        $untouched->refresh();
        $this->assertSame('AskDoc RAG', $untouched->title);
        $this->assertStringContainsString('big brother of AskDoc Lite', $untouched->body);
        $this->assertStringStartsWith('Intro.', $untouched->body);
        $this->assertStringContainsString('large libraries', $untouched->tagline);

        $untouched->delete();
        $edited = Project::create(['title' => 'My AskDoc', 'tagline' => 'Mine', 'body' => 'My words.'] + $original);
        $this->runRenameMigration();
        $edited->refresh();
        $this->assertSame('My AskDoc', $edited->title);
        $this->assertSame('Mine', $edited->tagline);
        $this->assertSame('My words.', $edited->body);
    }
}
