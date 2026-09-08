<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AuthApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_a_new_member_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Reader',
            'email' => 'jane@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('user.role', User::ROLE_MEMBER);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => User::ROLE_MEMBER,
        ]);
    }

    public function test_registration_rejects_duplicate_emails(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'John',
            'email' => 'taken@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_a_user_can_login_and_use_the_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => bcrypt('secret-pass'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'reader@example.com',
            'password' => 'secret-pass',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);

        $this->withHeader('Authorization', 'Bearer '.$response->json('token'))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'reader@example.com');
    }

    public function test_login_with_wrong_credentials_fails(): void
    {
        User::factory()->create(['email' => 'reader@example.com', 'password' => bcrypt('secret-pass')]);

        $this->postJson('/api/login', [
            'email' => 'reader@example.com',
            'password' => 'nope-nope',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_unauthenticated_requests_to_me_endpoint_fail(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }
}
