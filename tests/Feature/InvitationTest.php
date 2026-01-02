<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\InvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->user->companies()->attach($this->company->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);

        $this->actingAs($this->user);
        session(['current_tenant_id' => $this->company->id]);
    }

    public function test_owner_can_send_invitation(): void
    {
        Notification::fake();

        $response = $this->post(route('settings.users.invite'), [
            'email' => 'newuser@example.com',
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invitations', [
            'email' => 'newuser@example.com',
            'company_id' => $this->company->id,
            'role' => 'member',
        ]);

        Notification::assertSentOnDemand(InvitationNotification::class);
    }

    public function test_cannot_invite_existing_company_member(): void
    {
        $existingUser = User::factory()->create();
        $this->company->users()->attach($existingUser->id, ['role' => 'member']);

        $response = $this->post(route('settings.users.invite'), [
            'email' => $existingUser->email,
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_send_duplicate_pending_invitation(): void
    {
        Invitation::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'pending@example.com',
            'invited_by' => $this->user->id,
        ]);

        $response = $this->post(route('settings.users.invite'), [
            'email' => 'pending@example.com',
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_invitation_page_can_be_accessed(): void
    {
        $invitation = Invitation::factory()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
        ]);

        // Logout to test as guest
        auth()->logout();

        $response = $this->get(route('invitations.accept', $invitation->token));

        $response->assertStatus(200);
        $response->assertSee($this->company->name);
    }

    public function test_expired_invitation_redirects_with_error(): void
    {
        $invitation = Invitation::factory()->expired()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
        ]);

        auth()->logout();

        $response = $this->get(route('invitations.accept', $invitation->token));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_accepted_invitation_redirects_with_info(): void
    {
        $invitation = Invitation::factory()->accepted()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
        ]);

        auth()->logout();

        $response = $this->get(route('invitations.accept', $invitation->token));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
    }

    public function test_new_user_can_accept_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
            'email' => 'newuser@example.com',
            'role' => 'accountant',
        ]);

        auth()->logout();

        $response = $this->post(route('invitations.accept.store', $invitation->token), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($this->company->users()->where('user_id', $newUser->id)->exists());

        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_existing_user_can_accept_invitation_with_password(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
        ]);

        $invitation = Invitation::factory()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
            'email' => 'existing@example.com',
            'role' => 'member',
        ]);

        auth()->logout();

        $response = $this->post(route('invitations.accept.store', $invitation->token), [
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertTrue($this->company->users()->where('user_id', $existingUser->id)->exists());
    }

    public function test_logged_in_user_can_accept_invitation(): void
    {
        $otherUser = User::factory()->create();
        $otherCompany = Company::factory()->create();
        $otherUser->companies()->attach($otherCompany->id, ['role' => 'owner', 'is_default' => true]);

        $invitation = Invitation::factory()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
            'email' => $otherUser->email,
            'role' => 'member',
        ]);

        $this->actingAs($otherUser);

        $response = $this->post(route('invitations.accept.store', $invitation->token));

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue($this->company->users()->where('user_id', $otherUser->id)->exists());
    }

    public function test_invitation_can_be_resent(): void
    {
        Notification::fake();

        $invitation = Invitation::factory()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post(route('settings.invitations.resend', $invitation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $invitation->refresh();
        $this->assertTrue($invitation->expires_at->isAfter(now()->addDays(6)));

        Notification::assertSentOnDemand(InvitationNotification::class);
    }

    public function test_invitation_can_be_cancelled(): void
    {
        $invitation = Invitation::factory()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
        ]);

        $response = $this->delete(route('settings.invitations.cancel', $invitation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_accepted_invitation_cannot_be_cancelled(): void
    {
        $invitation = Invitation::factory()->accepted()->create([
            'company_id' => $this->company->id,
            'invited_by' => $this->user->id,
        ]);

        $response = $this->delete(route('settings.invitations.cancel', $invitation));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
        ]);
    }
}
