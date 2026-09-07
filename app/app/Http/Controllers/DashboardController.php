<?php

namespace App\Http\Controllers;

use App\Models\BookLoan;
use App\Services\StatsService;

class DashboardController extends Controller
{
    private StatsService $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Role-aware dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isStaff()) {
            $stats = $this->statsService->summary();

            $activeLoans = BookLoan::with(['book', 'user'])
                ->active()
                ->latest()
                ->limit(10)
                ->get();

            $overdueCount = BookLoan::overdue()->count();

            return view('dashboard.index', compact('user', 'stats', 'activeLoans', 'overdueCount'));
        }

        $currentLoans = BookLoan::with('book')
            ->where('user_id', $user->id)
            ->active()
            ->latest()
            ->get();

        $history = BookLoan::with('book')
            ->where('user_id', $user->id)
            ->returned()
            ->latest('returned_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('user', 'currentLoans', 'history'));
    }
}
