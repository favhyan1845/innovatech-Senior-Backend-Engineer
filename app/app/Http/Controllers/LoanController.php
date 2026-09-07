<?php

namespace App\Http\Controllers;

use App\Exceptions\LibraryRuleException;
use App\Models\Book;
use App\Models\BookLoan;
use App\Services\LibraryService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    private LibraryService $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * Check a book out for the authenticated user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request, Book $book)
    {
        try {
            $this->libraryService->checkout($book, $request->user());
            session()->flash('success', 'The book was checked out successfully. Enjoy your reading!');
        } catch (LibraryRuleException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Return a book (self-service or staff).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkin(Request $request, BookLoan $loan)
    {
        $user = $request->user();

        if (! $user->isStaff() && $loan->user_id !== $user->id) {
            abort(403, 'You cannot return a loan that belongs to another user.');
        }

        try {
            $this->libraryService->checkIn($loan, $user->isStaff() ? $user : null);
            session()->flash('success', 'The book was returned successfully.');
        } catch (LibraryRuleException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }
}
