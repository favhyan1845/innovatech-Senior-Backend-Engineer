<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\LibraryRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\LibraryService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    private LibraryService $libraryService;

    private RecommendationService $recommendationService;

    public function __construct(LibraryService $libraryService, RecommendationService $recommendationService)
    {
        $this->libraryService = $libraryService;
        $this->recommendationService = $recommendationService;
    }

    /**
     * List books with search, filters, sorting and pagination.
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

        if ($request->filled('author')) {
            $query->where('author', 'like', '%'.trim((string) $request->input('author')).'%');
        }

        if ($request->boolean('available')) {
            $query->available();
        }

        $this->applySort($query, $request->input('sort', 'newest'));

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);

        return BookResource::collection($query->paginate($perPage)->withQueryString());
    }

    /**
     * Show a single book.
     */
    public function show(Book $book)
    {
        $book->loadCount(['loans', 'activeLoans']);

        return new BookResource($book);
    }

    /**
     * Create a book (librarian/admin).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());

        return (new BookResource($book))
            ->additional(['message' => 'Book created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a book (librarian/admin).
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());

        return (new BookResource($book->loadCount(['loans', 'activeLoans'])))
            ->additional(['message' => 'Book updated successfully.']);
    }

    /**
     * Delete a book (librarian/admin).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Book $book)
    {
        try {
            $this->libraryService->deleteBook($book);
        } catch (LibraryRuleException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Book deleted successfully.']);
    }

    /**
     * AI-flavoured recommendations ("more like this" for a book).
     */
    public function recommendations(Book $book, Request $request)
    {
        $limit = min(max((int) $request->input('limit', 6), 1), 12);
        $books = $this->recommendationService->similar($book, $limit);

        return BookResource::collection($books);
    }

    /**
     * Apply one of the whitelisted sort options.
     */
    private function applySort($query, string $sort): void
    {
        switch ($sort) {
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
            case 'available':
                $query->orderByRaw(
                    '(books.total_copies - (select count(*) from book_loans where book_loans.book_id = books.id and book_loans.returned_at is null)) desc'
                );
                break;
            default:
                $query->latest();
        }
    }
}
