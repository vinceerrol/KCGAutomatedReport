<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PlatformDataServiceInterface;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PlatformController extends Controller
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService
    ) {}

    /**
     * Get marketplaces summary.
     */
    public function index(): JsonResponse
    {
        $today = Carbon::today()->format('Y-m-d');
        $platforms = $this->platformDataService->getPlatformSummary($today);

        return response()->json([
            'platforms' => $platforms,
            'date'      => Carbon::now()->format('F d, Y'),
        ]);
    }
}
