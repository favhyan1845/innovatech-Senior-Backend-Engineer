<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;

    private User $admin;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::factory()->librarian()->create();
        $this->admin = User::factory()->admin()->create();
        $this->member = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Innovatech Library');
        $this->get('/register')->assertOk();
    }

    public function test_member_dashboard_renders(): void
    {
        $book = Book::factory()->create();
        BookLoan::factory()->create(['book_id' => $book->id, 'user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee($book->title);
    }

    public function test_catalog_detail_page_renders_for_member(): void
    {
        $book = Book::factory()->create(['title' => 'A Book With A Title', 'total_copies' => 2]);
        BookLoan::factory()->create(['book_id' => $book->id, 'user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->get(route('catalog.show', $book))
            ->assertOk()
            ->assertSee('A Book With A Title')
            ->assertSee('Return this copy');
    }

    public function test_staff_book_management_pages_render(): void
    {
        Book::factory()->count(3)->create();

        $this->actingAs($this->librarian)
            ->get(route('admin.books.index'))
            ->assertOk();

        $this->actingAs($this->librarian)
            ->get(route('admin.books.create'))
            ->assertOk()
            ->assertDontSee('@selected', false)
            ->assertDontSee('@checked', false);
    }

    public function test_no_raw_blade_directives_leak_into_the_public_pages(): void
    {
        Book::factory()->create(['language' => 'en']);

        $home = $this->get('/')->assertOk();
        $home->assertDontSee('@selected', false);
        $home->assertDontSee('@checked', false);
        $home->assertSee('selected', false); // the language/category filters must select correctly

        $book = Book::first();
        $detail = $this->actingAs($this->member)->get(route('catalog.show', $book))->assertOk();
        $detail->assertDontSee('@selected', false);
    }

    public function test_loans_management_page_renders(): void
    {
        $loan = BookLoan::factory()->create([
            'book_id' => Book::factory()->create()->id,
            'user_id' => $this->member->id,
        ]);

        $this->actingAs($this->librarian)
            ->get(route('admin.loans.index'))
            ->assertOk()
            ->assertSee($loan->book->title);
    }

    public function test_admin_user_management_renders_and_members_are_forbidden(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($this->member->email);

        $this->actingAs($this->member)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_librarian_cannot_access_admin_only_users_page(): void
    {
        $this->actingAs($this->librarian)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_a_member_can_borrow_and_return_via_the_web_ui(): void
    {
        $book = Book::factory()->create(['total_copies' => 1]);

        $this->actingAs($this->member)
            ->post(route('loans.checkout', $book))
            ->assertRedirect();

        $this->assertDatabaseCount('book_loans', 1);

        $loan = BookLoan::first();

        $this->actingAs($this->member)
            ->post(route('loans.checkin', $loan))
            ->assertRedirect();

        $this->assertNotNull($loan->fresh()->returned_at);
    }
}
