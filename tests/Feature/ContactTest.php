<?php

namespace Tests\Feature;

use App\Mail\NewMessage;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private array $valid = [
        'name' => 'Ana Ruiz', 'email' => 'ana@example.com', 'company' => 'Demo Co',
        'project_type' => 'web_app', 'budget' => '1k_5k',
        'body' => 'We need a booking app for our three locations by March.',
    ];

    public function test_message_is_saved_and_emailed_to_the_contact_address(): void
    {
        Mail::fake();
        Setting::put(['email' => 'me@example.test']);

        $this->from('/')->post('/contact', $this->valid)->assertRedirect('/')->assertSessionHas('contact_sent', true);

        $m = Message::firstOrFail();
        $this->assertSame('Ana Ruiz', $m->name);
        $this->assertTrue($m->emailed);
        Mail::assertSent(NewMessage::class, fn ($mail) => $mail->hasTo('me@example.test') && $mail->hasReplyTo('ana@example.com') && $mail->hasSubject('[Portfolio] New enquiry from Ana Ruiz'));
    }

    public function test_mail_failure_still_keeps_the_message(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Resend down'));

        $this->post('/contact', $this->valid)->assertSessionHas('contact_sent', true);

        $this->assertFalse(Message::firstOrFail()->emailed);
    }

    public function test_validation_messages(): void
    {
        $this->post('/contact', ['name' => '', 'email' => 'nope', 'body' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'body']);
        $this->assertSame(0, Message::count());
    }

    public function test_honeypot_drops_bots_silently(): void
    {
        Mail::fake();
        $this->post('/contact', $this->valid + ['website' => 'http://spam.example'])->assertSessionHas('contact_sent', true);
        $this->assertSame(0, Message::count());
        Mail::assertNothingSent();
    }

    public function test_rate_limited_after_three_messages(): void
    {
        Mail::fake();
        foreach (range(1, 3) as $i) {
            $this->post('/contact', $this->valid)->assertSessionHasNoErrors();
        }
        $this->post('/contact', $this->valid)->assertSessionHasErrors('body');
        $this->assertSame(3, Message::count());
    }

    public function test_form_is_closed_to_upwork_and_fiverr_visitors(): void
    {
        $this->get('/?via=upwork');
        $this->post('/contact', $this->valid)->assertNotFound();

        $this->get('/?via=fiverr');
        $this->post('/contact', $this->valid)->assertNotFound();

        $this->assertSame(0, Message::count());
    }

    public function test_admin_sees_marks_read_and_deletes_messages(): void
    {
        $m = Message::create($this->valid);
        $admin = User::create(['name' => 'A', 'email' => 'a@example.test', 'password' => bcrypt('long-enough-password')]);
        $this->actingAs($admin);

        $this->get('/admin/messages')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Messages')->where('messages.0.name', 'Ana Ruiz')->where('unreadMessages', 1));

        $this->patch("/admin/messages/{$m->id}/read");
        $this->assertNotNull($m->fresh()->read_at);

        $this->delete("/admin/messages/{$m->id}");
        $this->assertSame(0, Message::count());
    }

    public function test_guests_cannot_read_messages(): void
    {
        $this->get('/admin/messages')->assertRedirect('/admin/login');
    }

    public function test_upwork_profile_link_is_filled_in(): void
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('profile.upwork_url', 'https://www.upwork.com/freelancers/~01294f831c6e9e058c'));
    }
}
