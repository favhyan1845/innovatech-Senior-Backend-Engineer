<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;

class StatsService
{
    /**
     * Aggregated statistics used by the dashboard and the /api/stats endpoint.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $totalCopies = (int) Book::sum('total_copies');
        $activeLoans = BookLoan::active()->count();
        $availableCopies = max(0, $totalCopies - $activeLoans);

        return [
            'total_books' => (int) Book::count(),
            'total_copies' => $totalCopies,
            'available_copies' => $availableCopies,
            'active_loans' => $activeLoans,
            'overdue_loans' => BookLoan::overdue()->count(),
            'returned_loans' => BookLoan::returned()->count(),
            'total_members' => (int) User::where('role', User::ROLE_MEMBER)->count(),
            'total_staff' => (int) User::whereIn('role', [User::ROLE_LIBRARIAN, User::ROLE_ADMIN])->count(),
            'top_categories' => Book::query()
                ->whereNotNull('category')
                ->selectRaw('category, count(*) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'category')
                ->all(),
            'recent_loans' => BookLoan::with(['book', 'user'])
                ->latest()
                ->limit(6)
                ->get(),
        ];
    }
}
