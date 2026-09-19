<?php

namespace App\Http\Controllers;

use App\Contracts\PlatformDataServiceInterface;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService
    ) {}

    /**
     * Display a listing of connected shops grouped by platform.
     */
    public function index(): Response
    {
        $today = Carbon::today()->format('Y-m-d');
        $shops = $this->platformDataService->getShopSummary($today);
        $platforms = $this->platformDataService->getPlatforms();

        return Inertia::render('Shops/Index', [
            'shops' => $shops,
            'platforms' => $platforms,
            'date' => Carbon::now()->format('F d, Y'),
        ]);
    }
}
