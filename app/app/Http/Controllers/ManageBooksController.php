<?php

namespace App\Http\Controllers;

use App\Exceptions\LibraryRuleException;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Services\LibraryService;
use Illuminate\Http\Request;

class ManageBooksController extends Controller
{
    private LibraryService $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * Staff list of books with quick search.
     */
    public function index(Request $request)
    {
        $query = Book::query()->withCount(['loans', 'activeLoans']);

        if ($request->filled('q')) {
            $query->search(trim((string) $request->input('q')));
        }

        $books = $query->latest()->paginate(15)->withQueryString();

        return view('books.index', compact('books'));
    }

    public function create()
    {
        return view('books.form', ['book' => null]);
    }

    public function store(StoreBookRequest $request)
    {
        Book::create($request->validated());

        session()->flash('success', 'Book created successfully.');

        return redirect()->route('admin.books.index');
    }

    public function edit(Book $book)
    {
        return view('books.form', ['book' => $book]);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());

        session()->flash('success', 'Book updated successfully.');

        return redirect()->route('admin.books.index');
    }

    public function destroy(Book $book)
    {
        try {
            $this->libraryService->deleteBook($book);
            session()->flash('success', 'Book deleted successfully.');
        } catch (LibraryRuleException $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('admin.books.index');
    }
}
