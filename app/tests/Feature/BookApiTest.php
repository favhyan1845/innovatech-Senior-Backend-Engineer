<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\ApiTestCase;

class BookApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_catalog_is_public(): void
    {
        Book::factory()->count(3)->create();

        $this->getJson('/api/books')->assertOk()->assertJsonCount(3, 'data');

        $book = Book::first();
        $this->getJson("/api/books/{$book->id}")->assertOk()->assertJsonPath('data.title', $book->title);
    }

    public function test_search_by_title_author_isbn_and_category(): void
    {
        Book::factory()->create(['title' => 'The Art of Laravel', 'author' => 'Jane Doe']);
        Book::factory()->count(4)->create(['title' => 'Another Book', 'author' => 'Someone Else']);

        $this->getJson('/api/books?q=Laravel')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/books?q=Jane%20Doe')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/books?q=9780132350884')->assertOk();

        $book = Book::factory()->create(['category' => 'Mystery', 'title' => 'Hidden Clues']);
        $this->getJson('/api/books?category=Mystery')->assertOk()->assertJsonCount(1, 'data');

        $this->getJson("/api/books/{$book->id}/recommendations")->assertOk();
    }

    public function test_guests_and_members_cannot_create_books(): void
    {
        $payload = Book::factory()->raw();

        $this->postJson('/api/books', $payload)->assertStatus(401);

        $member = User::factory()->create();
        Sanctum::actingAs($member, []);

        $this->postJson('/api/books', $payload)->assertStatus(403);
    }

    public function test_librarian_and_admin_can_manage_books(): void
    {
        $librarian = User::factory()->librarian()->create();
        Sanctum::actingAs($librarian, []);

        $payload = Book::factory()->raw(['title' => 'Domain-Driven Design', 'total_copies' => 2]);

        $this->postJson('/api/books', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Domain-Driven Design');

        $book = Book::where('title', 'Domain-Driven Design')->firstOrFail();

        $this->putJson("/api/books/{$book->id}", ['author' => 'Eric Evans'])
            ->assertOk()
            ->assertJsonPath('data.author', 'Eric Evans');

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin, []);
        $this->deleteJson("/api/books/{$book->id}")->assertOk();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_isbn_must_be_unique(): void
    {
        Book::factory()->create(['isbn' => '9780132350884']);

        $librarian = User::factory()->librarian()->create();
        Sanctum::actingAs($librarian, []);

        $this->postJson('/api/books', Book::factory()->raw(['isbn' => '9780132350884']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('isbn');
    }

    public function test_book_with_active_loans_cannot_be_deleted(): void
    {
        $book = Book::factory()->create(['total_copies' => 1]);
        BookLoan::factory()->create(['book_id' => $book->id, 'user_id' => User::factory()->create()->id]);

        $librarian = User::factory()->librarian()->create();
        Sanctum::actingAs($librarian, []);

        $this->deleteJson("/api/books/{$book->id}")->assertStatus(422);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_book_resource_exposes_human_readable_language(): void
    {
        $english = Book::factory()->create(['language' => 'en']);
        $regional = Book::factory()->create(['language' => 'es-ES']);

        $this->getJson("/api/books/{$english->id}")
            ->assertOk()
            ->assertJsonPath('data.language', 'en')
            ->assertJsonPath('data.language_label', 'English');

        $this->getJson("/api/books/{$regional->id}")
            ->assertOk()
            ->assertJsonPath('data.language', 'es-ES')
            ->assertJsonPath('data.language_label', 'Spanish');
    }

    public function test_available_filter_only_returns_books_with_free_copies(): void
    {
        $available = Book::factory()->create(['total_copies' => 2]);

        $borrowed = Book::factory()->create(['total_copies' => 1]);
        BookLoan::factory()->create(['book_id' => $borrowed->id, 'user_id' => User::factory()->create()->id]);

        $response = $this->getJson('/api/books?available=1');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($available->id));
        $this->assertFalse($ids->contains($borrowed->id));
    }
}
