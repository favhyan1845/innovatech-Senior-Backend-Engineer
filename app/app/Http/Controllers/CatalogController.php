<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    private RecommendationService $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Public catalog with search, category filter and sorting.
     */
    public function index(Request $request)
    {
        $query = Book::query()->withCount(['loans', 'activeLoans']);

        if ($request->filled('q')) {
            $query->search(trim((string) $request->input('q')));
        }

        if ($request->filled('category')) {
            $query->where('category', trim((string) $request->input('category')));
        }

        if ($request->boolean('available')) {
            $query->available();
        }

        switch ($request->input('sort', 'newest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title':
                $query->orderBy('title');
                break;
            case 'author':
                $query->orderBy('author');
                break;
            case 'popular':
                $query->orderByDesc('loans_count');
                break;
            default:
                $query->latest();
        }

        $books = $query->paginate(12)->withQueryString();

        $categories = Book::query()
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $featured = $this->recommendationService->popular(5);

        return view('catalog.index', compact('books', 'categories', 'featured'));
    }

    /**
     * Single book detail page.
     */
    public function show(Book $book)
    {
        $book->loadCount(['loans', 'activeLoans']);

        $similar = $this->recommendationService->similar($book, 4);

        $activeLoanForCurrentUser = auth()->check()
            ? $book->activeLoans()->where('user_id', auth()->id())->first()
            : null;

        return view('catalog.show', compact('book', 'similar', 'activeLoanForCurrentUser'));
    }
}
