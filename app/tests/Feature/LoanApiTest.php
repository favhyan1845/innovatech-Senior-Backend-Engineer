<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\ApiTestCase;

class LoanApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_a_member_can_check_a_book_out(): void
    {
        $book = Book::factory()->create(['total_copies' => 2]);
        $member = User::factory()->create();
        Sanctum::actingAs($member, []);

        $this->postJson("/api/books/{$book->id}/checkout")
            ->assertStatus(201)
            ->assertJsonPath('data.status', BookLoan::STATUS_ACTIVE)
            ->assertJsonPath('data.book.id', $book->id)
            ->assertJsonPath('data.user.id', $member->id);

        $this->assertDatabaseCount('book_loans', 1);
        $this->assertEquals(1, $book->fresh()->availableCopies());
    }

    public function test_a_member_cannot_check_out_the_same_book_twice(): void
    {
        $book = Book::factory()->create(['total_copies' => 2]);
        $member = User::factory()->create();
        Sanctum::actingAs($member, []);

        $this->postJson("/api/books/{$book->id}/checkout")->assertStatus(201);

        $this->postJson("/api/books/{$book->id}/checkout")
            ->assertStatus(422)
            ->assertJsonPath('message', 'You already have an active loan for this book.');
    }

    public function test_checkout_is_rejected_when_all_copies_are_out(): void
    {
        $book = Book::factory()->create(['total_copies' => 1]);
        $otherMember = User::factory()->create();
        BookLoan::factory()->create(['book_id' => $book->id, 'user_id' => $otherMember->id]);

        $member = User::factory()->create();
        Sanctum::actingAs($member, []);

        $this->postJson("/api/books/{$book->id}/checkout")
            ->assertStatus(422)
            ->assertJsonPath('message', 'No copies of “'.$book->title.'” are currently available.');
    }

    public function test_a_member_can_return_their_own_loan(): void
    {
        $loan = BookLoan::factory()->create([
            'book_id' => Book::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
        ]);

        Sanctum::actingAs($loan->user, []);

        $this->postJson("/api/loans/{$loan->id}/check-in")
            ->assertOk()
            ->assertJsonPath('data.status', BookLoan::STATUS_RETURNED);

        $this->assertNotNull($loan->fresh()->returned_at);
    }

    public function test_a_member_cannot_return_someone_elses_loan(): void
    {
        $loan = BookLoan::factory()->create([
            'book_id' => Book::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
        ]);

        Sanctum::actingAs(User::factory()->create(), []);

        $this->postJson("/api/loans/{$loan->id}/check-in")->assertStatus(403);
    }

    public function test_double_checkin_is_rejected(): void
    {
        $loan = BookLoan::factory()->returned()->create([
            'book_id' => Book::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
        ]);

        Sanctum::actingAs($loan->user, []);

        $this->postJson("/api/loans/{$loan->id}/check-in")
            ->assertStatus(422)
            ->assertJsonPath('message', 'This book has already been returned.');
    }

    public function test_staff_can_check_out_on_behalf_of_a_member(): void
    {
        $book = Book::factory()->create();
        $member = User::factory()->create();
        $librarian = User::factory()->librarian()->create();
        Sanctum::actingAs($librarian, []);

        $this->postJson("/api/books/{$book->id}/checkout", ['user_id' => $member->id])
            ->assertStatus(201)
            ->assertJsonPath('data.user.id', $member->id);
    }

    public function test_member_loan_list_is_restricted_to_their_own_loans(): void
    {
        $member = User::factory()->create();
        BookLoan::factory()->create(['book_id' => Book::factory()->create()->id, 'user_id' => $member->id]);
        BookLoan::factory()->create(['book_id' => Book::factory()->create()->id, 'user_id' => User::factory()->create()->id]);

        Sanctum::actingAs($member, []);

        $this->getJson('/api/loans')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_overdue_filter_returns_only_overdue_loans(): void
    {
        $librarian = User::factory()->librarian()->create();
        $overdue = BookLoan::factory()->create([
            'book_id' => Book::factory()->create()->id,
            'user_id' => $librarian->id,
            'borrowed_at' => now()->subDays(30),
            'due_at' => now()->subDays(16),
        ]);

        BookLoan::factory()->create([
            'book_id' => Book::factory()->create()->id,
            'user_id' => $librarian->id,
            'borrowed_at' => now()->subDays(2),
            'due_at' => now()->addDays(12),
        ]);

        Sanctum::actingAs($librarian, []);

        $this->getJson('/api/loans?status=overdue')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $overdue->id);
    }
}
