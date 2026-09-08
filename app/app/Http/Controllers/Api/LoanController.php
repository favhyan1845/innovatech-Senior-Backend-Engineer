<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\LibraryRuleException;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookLoanResource;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
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
     * Check a book out for the authenticated member (or, for staff, on behalf of a member).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request, Book $book)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'days' => ['nullable', 'integer', 'between:1,30'],
        ]);

        $member = $request->user();

        if (! empty($data['user_id'])) {
            if (! $member->isStaff()) {
                return response()->json(['message' => 'You cannot check books out on behalf of another user.'], 403);
            }

            $member = User::where('role', User::ROLE_MEMBER)->findOrFail($data['user_id']);
        }

        try {
            $loan = $this->libraryService->checkout(
                $book,
                $member,
                $request->user()->isStaff() ? $request->user() : null,
                $data['days'] ?? null
            );

            return (new BookLoanResource($loan->load(['book', 'user'])))
                ->additional(['message' => 'Book checked out successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (LibraryRuleException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mark a loan as returned (self-service for members, any loan for staff).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIn(Request $request, BookLoan $loan)
    {
        $user = $request->user();

        if (! $user->isStaff() && $loan->user_id !== $user->id) {
            return response()->json(['message' => 'You cannot return a loan that belongs to another user.'], 403);
        }

        try {
            $loan = $this->libraryService->checkIn($loan, $user->isStaff() ? $user : null);

            return (new BookLoanResource($loan->load(['book', 'user'])))
                ->additional(['message' => 'Book returned successfully.']);
        } catch (LibraryRuleException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List loans. Members only see their own; staff may filter by user/status.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = BookLoan::query()
            ->with(['book', 'user'])
            ->latest();

        if (! $user->isStaff()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        switch ($request->input('status')) {
            case BookLoan::STATUS_OVERDUE:
                $query->overdue();
                break;
            case BookLoan::STATUS_RETURNED:
                $query->returned();
                break;
            case BookLoan::STATUS_ACTIVE:
                $query->active();
                break;
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        return BookLoanResource::collection($query->paginate($perPage)->withQueryString());
    }
}
