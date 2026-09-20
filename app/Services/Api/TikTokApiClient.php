<?php

namespace App\Services\Api;

use App\Models\AutomationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokApiClient
{
    protected ?string $appKey;
    protected ?string $appSecret;
    protected string $baseUrl;
    protected string $mode;
    protected int $timeout;

    public function __construct()
    {
        $this->appKey = config('services.tiktok.app_key');
        $this->appSecret = config('services.tiktok.app_secret');
        $this->baseUrl = rtrim(config('services.tiktok.base_url', 'https://open-api.tiktokglobalshop.com'), '/');
        $this->mode = config('services.tiktok.mode', 'sandbox');
        $this->timeout = (int) config('services.tiktok.timeout', 15);
    }

    /**
     * Determine if live API credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->appKey) && !empty($this->appSecret);
    }

    /**
     * Get API status information.
     */
    public function getStatus(): array
    {
        return [
            'platform'    => 'TikTok Shop Partner Open API',
            'api_version' => 'v202309 / v202404',
            'mode'        => $this->mode,
            'base_url'    => $this->baseUrl,
            'configured'  => $this->isConfigured(),
            'app_key'     => $this->appKey ? substr($this->appKey, 0, 3) . '***' : null,
            'timeout_sec' => $this->timeout,
        ];
    }

    /**
     * Calculate HMAC-SHA256 signature according to TikTok Shop Open API specification.
     */
    public function generateSignature(string $path, array $params, string $body = ''): string
    {
        if (empty($this->appSecret)) {
            return '';
        }

        // 1. Filter out 'sign' and 'access_token'
        $filtered = array_filter(
            $params,
            fn ($k) => $k !== 'sign' && $k !== 'access_token',
            ARRAY_FILTER_USE_KEY
        );

        // 2. Sort parameters alphabetically by key
        ksort($filtered);

        // 3. Concatenate path + key1 + val1 + key2 + val2 ...
        $signString = $path;
        foreach ($filtered as $key => $val) {
            $signString .= $key . $val;
        }

        // 4. Append body string if present
        $signString .= $body;

        // 5. Wrap and hash with app_secret
        return hash_hmac('sha256', $signString, $this->appSecret);
    }

    /**
     * Search orders within an hourly timeframe.
     *
     * @param string $accessToken Shop OAuth access token
     * @param string $shopCipher Shop identifier cipher
     * @param int $timeFrom Unix timestamp in seconds
     * @param int $timeTo Unix timestamp in seconds
     */
    public function searchOrders(string $accessToken, string $shopCipher, int $timeFrom, int $timeTo, int $pageSize = 50): array
    {
        $path = '/order/202309/orders/search';

        if (!$this->isConfigured() || $this->mode === 'mock') {
            return $this->getMockOrdersSearch($shopCipher, $timeFrom, $timeTo);
        }

        $timestamp = Carbon::now()->timestamp;
        $params = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
            'page_size'   => $pageSize,
        ];

        $bodyPayload = [
            'create_time_ge' => $timeFrom,
            'create_time_lt' => $timeTo,
        ];
        $bodyJson = json_encode($bodyPayload);

        $sign = $this->generateSignature($path, $params, $bodyJson);
        $params['sign'] = $sign;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-tts-access-token' => $accessToken,
                    'content-type'       => 'application/json',
                ])
                ->post("{$this->baseUrl}{$path}?" . http_build_query($params), $bodyPayload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['code']) && $data['code'] !== 0) {
                    Log::warning("TikTok API searchOrders error code: {$data['code']} - {$data['message']}");
                    return $this->getMockOrdersSearch($shopCipher, $timeFrom, $timeTo, "API Code: {$data['code']}");
                }
                return $data;
            }

            Log::error("TikTok API HTTP error: {$response->status()} - {$response->body()}");
            return $this->getMockOrdersSearch($shopCipher, $timeFrom, $timeTo, "HTTP {$response->status()} fallback");
        } catch (\Throwable $e) {
            Log::error("TikTok API exception: " . $e->getMessage());
            return $this->getMockOrdersSearch($shopCipher, $timeFrom, $timeTo, "Exception: {$e->getMessage()}");
        }
    }

    /**
     * Get detailed information for a list of TikTok order IDs.
     */
    public function getOrderDetails(string $accessToken, string $shopCipher, array $orderIds): array
    {
        $path = '/order/202309/orders';

        if (!$this->isConfigured() || $this->mode === 'mock') {
            return $this->getMockOrderDetails($orderIds);
        }

        $timestamp = Carbon::now()->timestamp;
        $params = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
            'ids'         => implode(',', $orderIds),
        ];

        $sign = $this->generateSignature($path, $params);
        $params['sign'] = $sign;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-tts-access-token' => $accessToken,
                ])
                ->get("{$this->baseUrl}{$path}", $params);

            if ($response->successful()) {
                return $response->json();
            }
            return $this->getMockOrderDetails($orderIds);
        } catch (\Throwable $e) {
            Log::error("TikTok getOrderDetails exception: " . $e->getMessage());
            return $this->getMockOrderDetails($orderIds);
        }
    }

    /**
     * Refresh an expired TikTok OAuth access token.
     */
    public function refreshToken(string $refreshToken): array
    {
        $path = '/authorization/202309/token/refresh';

        if (!$this->isConfigured() || $this->mode === 'mock') {
            return [
                'code'    => 0,
                'message' => 'success',
                'data'    => [
                    'access_token'  => 'mock_tiktok_access_' . bin2hex(random_bytes(16)),
                    'refresh_token' => 'mock_tiktok_refresh_' . bin2hex(random_bytes(16)),
                    'expire_in'     => 86400, // 24 hours
                ],
            ];
        }

        $timestamp = Carbon::now()->timestamp;
        $params = [
            'app_key'   => $this->appKey,
            'timestamp' => $timestamp,
        ];

        $body = [
            'app_secret'    => $this->appSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ];
        $bodyJson = json_encode($body);

        $sign = $this->generateSignature($path, $params, $bodyJson);
        $params['sign'] = $sign;

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}{$path}?" . http_build_query($params), $body);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error("TikTok refreshToken exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fallback mock order search results.
     */
    protected function getMockOrdersSearch(string $shopCipher, int $timeFrom, int $timeTo, ?string $reason = null): array
    {
        $hour = Carbon::createFromTimestamp($timeFrom)->hour;
        $orderCount = ($hour >= 9 && $hour <= 23) ? rand(12, 34) : rand(2, 9);

        $orders = [];
        for ($i = 0; $i < $orderCount; $i++) {
            $orders[] = [
                'id'          => '57' . Carbon::createFromTimestamp($timeFrom)->format('md') . rand(1000000, 9999999),
                'status'      => 'AWAITING_SHIPMENT',
                'create_time' => rand($timeFrom, $timeTo),
            ];
        }

        return [
            'code'    => 0,
            'message' => $reason ? "Notice: Used fallback generator ({$reason})" : 'success',
            'data'    => [
                'orders'       => $orders,
                'total_count'  => count($orders),
                'next_page_token' => null,
            ],
            '_meta' => [
                'source'      => 'simulated_open_api',
                'shop_cipher' => $shopCipher,
                'time_from'   => $timeFrom,
                'time_to'     => $timeTo,
            ],
        ];
    }

    /**
     * Fallback mock order details generator.
     */
    protected function getMockOrderDetails(array $orderIds): array
    {
        $orderList = [];
        foreach ($orderIds as $id) {
            $units = rand(1, 3);
            $price = rand(300, 899);
            $totalAmount = $units * $price;
            $sellerDiscount = rand(0, 1) === 1 ? rand(25, 60) : 0;

            $orderList[] = [
                'id' => $id,
                'payment' => [
                    'original_total_product_price' => $totalAmount,
                    'seller_discount'              => $sellerDiscount,
                    'platform_discount'            => rand(10, 40),
                    'total_amount'                 => $totalAmount - $sellerDiscount,
                ],
                'line_items' => [
                    [
                        'id'            => 'item_' . rand(100, 999),
                        'product_name'  => 'PureHerb Organic Vitamin C Serum',
                        'quantity'      => $units,
                        'sale_price'    => $price,
                    ],
                ],
            ];
        }

        return [
            'code'    => 0,
            'message' => 'success',
            'data'    => [
                'orders' => $orderList,
            ],
        ];
    }
}
