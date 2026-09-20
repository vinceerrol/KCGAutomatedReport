<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PlatformDataServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\Api\ShopeeApiClient;
use App\Services\Api\TikTokApiClient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PlatformController extends Controller
{
    public function __construct(
        protected PlatformDataServiceInterface $platformDataService,
        protected ShopeeApiClient $shopeeClient,
        protected TikTokApiClient $tikTokClient
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

    /**
     * Get Open API connectors configuration, credentials health, and synchronization status.
     */
    public function connectorsStatus(): JsonResponse
    {
        $shopeeShops = Shop::whereHas('platform', fn ($q) => $q->where('code', 'shopee'))->get();
        $tikTokShops = Shop::whereHas('platform', fn ($q) => $q->where('code', 'tiktok'))->get();

        return response()->json([
            'shopee' => array_merge($this->shopeeClient->getStatus(), [
                'shops_count'          => $shopeeShops->count(),
                'shops_configured'     => $shopeeShops->filter->hasValidApiCredentials()->count(),
                'auth_type'            => 'HMAC-SHA256 Partner Key + OAuth 2.0',
                'orders_endpoint'      => '/api/v2/order/get_order_list',
                'order_detail_endpoint'=> '/api/v2/order/get_order_detail',
                'refresh_endpoint'     => '/api/v2/public/refresh_token',
            ]),
            'tiktok' => array_merge($this->tikTokClient->getStatus(), [
                'shops_count'          => $tikTokShops->count(),
                'shops_configured'     => $tikTokShops->filter->hasValidApiCredentials()->count(),
                'auth_type'            => 'HMAC-SHA256 App Secret + OAuth 2.0 (x-tts-access-token)',
                'orders_endpoint'      => '/order/202309/orders/search',
                'order_detail_endpoint'=> '/order/202309/orders',
                'refresh_endpoint'     => '/authorization/202309/token/refresh',
            ]),
            'server_time_pht' => Carbon::now()->format('Y-m-d h:i:s A') . ' PHT',
        ]);
    }
}
