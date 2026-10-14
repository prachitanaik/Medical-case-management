<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** Required test 1: valid login succeeds. */
    public function test_valid_login_succeeds(): void
    {
        $user = User::factory()->admin()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_valid_api_login_returns_a_token(): void
    {
        $user = User::factory()->viewer()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.role', 'viewer')
            ->assertJsonStructure(['success', 'data' => ['token', 'user'], 'message']);
    }

    /** Required test 2: invalid login is rejected. */
    public function test_invalid_login_is_rejected(): void
    {
        $user = User::factory()->admin()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Invalid email or password.']);

        $this->assertGuest();
    }

    public function test_invalid_api_login_is_rejected_with_401(): void
    {
        $user = User::factory()->admin()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_api_login_validates_required_fields(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** Required test 3: a guest cannot open case pages or APIs. */
    public function test_guest_cannot_open_case_pages(): void
    {
        $case = MedicalCase::factory()->create();

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/cases')->assertRedirect(route('login'));
        $this->get('/cases/create')->assertRedirect(route('login'));
        $this->get("/cases/{$case->id}")->assertRedirect(route('login'));
        $this->get("/cases/{$case->id}/edit")->assertRedirect(route('login'));
    }

    public function test_guest_cannot_use_case_api(): void
    {
        $case = MedicalCase::factory()->create();

        $this->getJson('/api/cases')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
        $this->getJson("/api/cases/{$case->id}")->assertUnauthorized();
        $this->postJson('/api/cases', [])->assertUnauthorized();
        $this->putJson("/api/cases/{$case->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/cases/{$case->id}")->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_user_can_log_out_of_the_web_app(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_api_logout_revokes_the_token(): void
    {
        $user = User::factory()->viewer()->create();

        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->json('data.token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
