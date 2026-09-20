<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PlatformDataServiceInterface;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService
    ) {}

    /**
     * Get shops directory and platform counts.
     */
    public function index(): JsonResponse
    {
        $today = Carbon::today()->format('Y-m-d');
        $shops = $this->platformDataService->getShopSummary($today);
        $platforms = $this->platformDataService->getPlatforms();

        return response()->json([
            'shops'     => $shops,
            'platforms' => $platforms,
            'date'      => Carbon::now()->format('F d, Y'),
        ]);
    }
}
