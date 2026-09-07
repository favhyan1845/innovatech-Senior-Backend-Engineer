<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookLoanResource;
use App\Services\StatsService;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    private StatsService $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Library statistics used by dashboards and mobile clients.
     */
    public function summary()
    {
        $summary = $this->statsService->summary();

        $summary['recent_loans'] = BookLoanResource::collection($summary['recent_loans']);

        return response()->json(['data' => $summary]);
    }
}
