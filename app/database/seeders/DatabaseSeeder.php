<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $password = Hash::make('password');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@library.test',
            'password' => $password,
            'role' => User::ROLE_ADMIN,
        ]);

        $librarian = User::create([
            'name' => 'Librarian User',
            'email' => 'librarian@library.test',
            'password' => $password,
            'role' => User::ROLE_LIBRARIAN,
        ]);

        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@library.test',
            'password' => $password,
            'role' => User::ROLE_MEMBER,
        ]);

        $extraMembers = User::factory()->count(6)->create();

        foreach ($this->catalog() as $data) {
            Book::create($data);
        }

        $this->seedLoanHistory($member, $extraMembers, $librarian);
    }

    /**
     * Sample catalog used by the demo.
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            ['title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'author' => 'Robert C. Martin', 'isbn' => '9780132350884', 'publisher' => 'Prentice Hall', 'category' => 'Technology', 'language' => 'en', 'published_year' => 2008, 'total_copies' => 3, 'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees. This book teaches you how to write clean, maintainable code.'],
            ['title' => 'Design Patterns: Elements of Reusable Object-Oriented Software', 'author' => 'Erich Gamma', 'isbn' => '9780201633610', 'publisher' => 'Addison-Wesley', 'category' => 'Technology', 'language' => 'en', 'published_year' => 1994, 'total_copies' => 2, 'description' => 'The classic reference for software design patterns and object-oriented programming.'],
            ['title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt', 'isbn' => '9780135957059', 'publisher' => 'Addison-Wesley', 'category' => 'Technology', 'language' => 'en', 'published_year' => 2019, 'total_copies' => 2, 'description' => 'A journey from apprentice to master, full of tips and best practices for software developers.'],
            ['title' => 'A Brief History of Time', 'author' => 'Stephen Hawking', 'isbn' => '9780553380163', 'publisher' => 'Bantam', 'category' => 'Science', 'language' => 'en', 'published_year' => 1988, 'total_copies' => 4, 'description' => 'Hawking\'s landmark work explores the origins of the universe, black holes and the nature of time.'],
            ['title' => 'Sapiens: A Brief History of Humankind', 'author' => 'Yuval Noah Harari', 'isbn' => '9780062316097', 'publisher' => 'Harper', 'category' => 'History', 'language' => 'en', 'published_year' => 2015, 'total_copies' => 3, 'description' => 'How did our species come to dominate the planet? A sweeping narrative of human history.'],
            ['title' => 'The Selfish Gene', 'author' => 'Richard Dawkins', 'isbn' => '9780198788607', 'publisher' => 'Oxford University Press', 'category' => 'Science', 'language' => 'en', 'published_year' => 1976, 'total_copies' => 2, 'description' => 'The gene-centred view of evolution, one of the most influential science books of all time.'],
            ['title' => 'Steve Jobs', 'author' => 'Walter Isaacson', 'isbn' => '9781451648539', 'publisher' => 'Simon & Schuster', 'category' => 'Biography', 'language' => 'en', 'published_year' => 2011, 'total_copies' => 2, 'description' => 'The exclusive biography of Apple co-founder Steve Jobs, based on over forty interviews.'],
            ['title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'isbn' => '9780547928227', 'publisher' => 'Houghton Mifflin', 'category' => 'Fantasy', 'language' => 'en', 'published_year' => 1937, 'total_copies' => 5, 'description' => 'Bilbo Baggins is swept into a quest to reclaim the lost Dwarf Kingdom of Erebor.'],
            ['title' => 'Dune', 'author' => 'Frank Herbert', 'isbn' => '9780441172719', 'publisher' => 'Ace Books', 'category' => 'Fiction', 'language' => 'en', 'published_year' => 1965, 'total_copies' => 4, 'description' => 'Set in the far future, Dune tells the story of Paul Atreides and the desert planet Arrakis.'],
            ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '9780451524935', 'publisher' => 'Signet', 'category' => 'Fiction', 'language' => 'en', 'published_year' => 1949, 'total_copies' => 4, 'description' => 'Winston Smith rewrites history under the watchful eye of Big Brother in a totalitarian state.'],
            ['title' => 'Atomic Habits', 'author' => 'James Clear', 'isbn' => '9780735211292', 'publisher' => 'Avery', 'category' => 'Self-help', 'language' => 'en', 'published_year' => 2018, 'total_copies' => 3, 'description' => 'An easy and proven way to build good habits and break bad ones.'],
            ['title' => 'Eloquent JavaScript', 'author' => 'Marijn Haverbeke', 'isbn' => '9781593279509', 'publisher' => 'No Starch Press', 'category' => 'Technology', 'language' => 'en', 'published_year' => 2018, 'total_copies' => 2, 'description' => 'A modern introduction to programming with JavaScript for beginners and professionals.'],
            ['title' => 'The Art of War', 'author' => 'Sun Tzu', 'isbn' => '9781599869773', 'publisher' => 'Simon & Brown', 'category' => 'History', 'language' => 'en', 'published_year' => 500, 'total_copies' => 2, 'description' => 'An ancient Chinese military treatise, still cited today for strategy and leadership.'],
            ['title' => 'Thinking, Fast and Slow', 'author' => 'Daniel Kahneman', 'isbn' => '9780374533557', 'publisher' => 'Farrar, Straus and Giroux', 'category' => 'Science', 'language' => 'en', 'published_year' => 2011, 'total_copies' => 2, 'description' => 'Nobel laureate Daniel Kahneman explains the two systems that drive the way we think.'],
        ];
    }

    /**
     * Create a realistic mix of active, overdue and returned loans.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $extraMembers
     */
    private function seedLoanHistory(User $member, $extraMembers, User $librarian): void
    {
        $books = Book::orderBy('id')->get();
        $allMembers = $extraMembers->push($member);

        // Active loans (some borrowed recently, one overdue)
        $loans = [
            ['book_id' => $books[0]->id, 'user_id' => $member->id, 'borrowed_at' => now()->subDays(3), 'due_at' => now()->addDays(11)],
            ['book_id' => $books[3]->id, 'user_id' => $extraMembers[0]->id, 'borrowed_at' => now()->subDays(7), 'due_at' => now()->addDays(7)],
            ['book_id' => $books[7]->id, 'user_id' => $extraMembers[1]->id, 'borrowed_at' => now()->subDays(1), 'due_at' => now()->addDays(13)],
            ['book_id' => $books[9]->id, 'user_id' => $member->id, 'borrowed_at' => now()->subDays(30), 'due_at' => now()->subDays(16)], // overdue
            ['book_id' => $books[4]->id, 'user_id' => $extraMembers[2]->id, 'borrowed_at' => now()->subDays(20), 'due_at' => now()->subDays(6)], // overdue
        ];

        foreach ($loans as $loan) {
            BookLoan::create($loan + ['handled_by' => $librarian->id]);
        }

        // Returned history for the main member
        $returned = [
            ['book_id' => $books[8]->id, 'user_id' => $member->id, 'borrowed_at' => now()->subDays(40), 'due_at' => now()->subDays(26)],
            ['book_id' => $books[10]->id, 'user_id' => $member->id, 'borrowed_at' => now()->subDays(25), 'due_at' => now()->subDays(11)],
            ['book_id' => $books[13]->id, 'user_id' => $member->id, 'borrowed_at' => now()->subDays(60), 'due_at' => now()->subDays(46)],
        ];

        foreach ($returned as $loan) {
            BookLoan::create($loan + [
                'handled_by' => $librarian->id,
                'returned_at' => now()->subDays(rand(2, 10)),
            ]);
        }

        // A few random returned loans for extra members
        foreach ($allMembers->take(3) as $i => $extra) {
            BookLoan::factory()->returned()->create([
                'book_id' => $books[$i + 5]->id,
                'user_id' => $extra->id,
                'handled_by' => $librarian->id,
            ]);
        }
    }
}
