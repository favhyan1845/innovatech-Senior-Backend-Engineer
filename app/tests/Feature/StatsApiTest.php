<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\ApiTestCase;

class StatsApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_stats_are_available_for_authenticated_users(): void
    {
        Book::factory()->count(3)->create(['total_copies' => 2]);

        $librarian = User::factory()->librarian()->create();
        Sanctum::actingAs($librarian, []);

        $this->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('data.total_books', 3)
            ->assertJsonPath('data.total_copies', 6)
            ->assertJsonStructure([
                'data' => [
                    'total_books', 'total_copies', 'available_copies',
                    'active_loans', 'overdue_loans', 'returned_loans',
                    'total_members', 'top_categories',
                ],
            ]);
    }

    public function test_admin_can_list_and_update_users(): void
    {
        User::factory()->count(3)->create();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin, []);

        $this->getJson('/api/admin/users')->assertOk()->assertJsonCount(4, 'data');

        $member = User::where('email', '!=', $admin->email)->firstOrFail();

        $this->putJson("/api/admin/users/{$member->id}", ['role' => User::ROLE_LIBRARIAN])
            ->assertOk()
            ->assertJsonPath('data.role', User::ROLE_LIBRARIAN);

        $this->assertDatabaseHas('users', ['id' => $member->id, 'role' => User::ROLE_LIBRARIAN]);
    }

    public function test_librarian_cannot_manage_users(): void
    {
        $librarian = User::factory()->librarian()->create();
        Sanctum::actingAs($librarian, []);

        $this->getJson('/api/admin/users')->assertStatus(403);
    }
}
