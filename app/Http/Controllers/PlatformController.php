<?php

namespace App\Http\Controllers;

use App\Contracts\PlatformDataServiceInterface;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PlatformController extends Controller
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService
    ) {}

    /**
     * Display a listing of supported platforms.
     */
    public function index(): Response
    {
        $today = Carbon::today()->format('Y-m-d');
        $platforms = $this->platformDataService->getPlatformSummary($today);

        return Inertia::render('Platforms/Index', [
            'platforms' => $platforms,
            'date' => Carbon::now()->format('F d, Y'),
        ]);
    }
}
