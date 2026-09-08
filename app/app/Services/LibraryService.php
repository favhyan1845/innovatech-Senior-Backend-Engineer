<?php

namespace App\Services;

use App\Exceptions\LibraryRuleException;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LibraryService
{
    /**
     * Check a book out (lend a copy to a member).
     *
     * @param  int|null  $days  Optional custom loan length.
     * @throws LibraryRuleException
     */
    public function checkout(Book $book, User $member, ?User $handler = null, ?int $days = null): BookLoan
    {
        $days = $days === null ? BookLoan::DEFAULT_LOAN_DAYS : max(1, min((int) $days, BookLoan::MAX_LOAN_DAYS));

        return DB::transaction(function () use ($book, $member, $handler, $days) {
            if ($book->availableCopies() <= 0) {
                throw new LibraryRuleException("No copies of “{$book->title}” are currently available.");
            }

            $alreadyBorrowed = $book->activeLoans()
                ->where('user_id', $member->id)
                ->exists();

            if ($alreadyBorrowed) {
                throw new LibraryRuleException('You already have an active loan for this book.');
            }

            return $book->loans()->create([
                'user_id' => $member->id,
                'handled_by' => $handler->id ?? null,
                'borrowed_at' => now(),
                'due_at' => now()->addDays($days),
            ]);
        });
    }

    /**
     * Check a loan back in (mark the copy as returned).
     *
     * @throws LibraryRuleException
     */
    public function checkIn(BookLoan $loan, ?User $handler = null): BookLoan
    {
        if ($loan->isReturned()) {
            throw new LibraryRuleException('This book has already been returned.');
        }

        $loan->forceFill([
            'returned_at' => now(),
            'handled_by' => $handler->id ?? $loan->handled_by,
        ])->save();

        return $loan->refresh();
    }

    /**
     * Delete a book safely. Books with active loans cannot be removed.
     *
     * @throws LibraryRuleException
     */
    public function deleteBook(Book $book): void
    {
        if ($book->activeLoans()->exists()) {
            throw new LibraryRuleException(
                'This book cannot be deleted because copies are currently checked out. Return them first.'
            );
        }

        $book->delete();
    }
}
