<?php

namespace App\Http\Controllers;

use App\Models\BookLoan;
use Illuminate\Http\Request;

class ManageLoansController extends Controller
{
    /**
     * Staff view of all loans with status filters.
     */
    public function index(Request $request)
    {
        $query = BookLoan::query()
            ->with(['book', 'user'])
            ->latest();

        $status = $request->input('status', 'active');

        switch ($status) {
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

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->whereHas('book', function ($b) use ($term) {
                    $b->where('title', 'like', "%{$term}%")
                        ->orWhere('author', 'like', "%{$term}%");
                })->orWhereHas('user', function ($u) use ($term) {
                    $u->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            });
        }

        $loans = $query->paginate(15)->withQueryString();

        $counts = [
            BookLoan::STATUS_ACTIVE => BookLoan::active()->count(),
            BookLoan::STATUS_OVERDUE => BookLoan::overdue()->count(),
            BookLoan::STATUS_RETURNED => BookLoan::returned()->count(),
        ];

        return view('loans.index', compact('loans', 'counts', 'status'));
    }
}
