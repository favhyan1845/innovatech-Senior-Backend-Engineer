<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Lightweight recommendation engine.
     *
     * It combines collaborative signals (books borrowed by similar readers),
     * content signals (favourite categories/author of the user) and popularity.
     * When there is no history at all it falls back to the most borrowed books.
     *
     * @return Collection<int, Book>
     */
    public function forUser(?User $user, int $limit = 6): Collection
    {
        if ($user === null) {
            return $this->popular($limit);
        }

        $borrowedBookIds = $user->loans()->pluck('book_id');

        if ($borrowedBookIds->isEmpty()) {
            return $this->popular($limit);
        }

        // Neighbours: other members who borrowed at least one of the same books.
        $neighbourIds = BookLoan::query()
            ->whereIn('book_id', $borrowedBookIds)
            ->where('user_id', '!=', $user->id)
            ->distinct()
            ->pluck('user_id');

        // Candidate books borrowed by neighbours, most popular first.
        $candidates = BookLoan::query()
            ->when($neighbourIds->isNotEmpty(), function ($q) use ($neighbourIds) {
                $q->whereIn('user_id', $neighbourIds);
            })
            ->whereNotIn('book_id', $borrowedBookIds)
            ->selectRaw('book_id, count(*) as total')
            ->groupBy('book_id')
            ->orderByDesc('total')
            ->limit($limit * 4)
            ->pluck('total', 'book_id');

        if ($candidates->isEmpty()) {
            return $this->popular($limit);
        }

        $favouriteCategories = $user->loans()
            ->with('book')
            ->get()
            ->pluck('book.category')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3);

        $favouriteAuthors = $user->loans()
            ->with('book')
            ->get()
            ->pluck('book.author')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3);

        $books = Book::whereIn('id', $candidates->keys())
            ->get()
            ->sortByDesc(function (Book $book) use ($candidates, $favouriteCategories, $favouriteAuthors) {
                $score = (int) $candidates[$book->id];
                if ($favouriteCategories->contains($book->category)) {
                    $score += 6;
                }
                if ($favouriteAuthors->contains($book->author)) {
                    $score += 4;
                }
                if ($book->isAvailable()) {
                    $score += 2;
                }

                return $score;
            })
            ->values();

        return $books->take($limit);
    }

    /**
     * The most borrowed books across the whole library.
     *
     * @return Collection<int, Book>
     */
    public function popular(int $limit = 6): Collection
    {
        $ids = BookLoan::query()
            ->selectRaw('book_id, count(*) as total')
            ->groupBy('book_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('book_id');

        $popular = Book::whereIn('id', $ids)->get();

        if ($popular->count() < $limit) {
            $fill = Book::whereNotIn('id', $popular->pluck('id'))
                ->latest()
                ->limit($limit - $popular->count())
                ->get();

            return $popular->concat($fill)->take($limit);
        }

        return $popular;
    }

    /**
     * Content-based "more like this" suggestions for a single book page.
     *
     * @return Collection<int, Book>
     */
    public function similar(Book $book, int $limit = 4): Collection
    {
        $candidates = Book::where('id', '!=', $book->id)
            ->where(function ($q) use ($book) {
                $q->where('category', $book->category)
                    ->orWhere('author', $book->author);
            })
            ->limit($limit * 3)
            ->get()
            ->sortByDesc(function (Book $candidate) use ($book) {
                $score = 0;
                if ($candidate->category === $book->category) {
                    $score += 3;
                }
                if ($candidate->author === $book->author) {
                    $score += 2;
                }
                if ($candidate->isAvailable()) {
                    $score += 1;
                }

                return $score;
            })
            ->values();

        if ($candidates->count() >= $limit) {
            return $candidates->take($limit);
        }

        $fill = Book::whereNotIn('id', $candidates->pluck('id')->push($book->id))
            ->latest()
            ->limit($limit - $candidates->count())
            ->get();

        return $candidates->concat($fill)->take($limit);
    }
}
