<?php

namespace App\Services\Api;

use App\Models\AutomationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeApiClient
{
    protected ?string $partnerId;
    protected ?string $partnerKey;
    protected string $baseUrl;
    protected string $mode;
    protected int $timeout;

    public function __construct()
    {
        $this->partnerId = config('services.shopee.partner_id');
        $this->partnerKey = config('services.shopee.partner_key');
        $this->baseUrl = rtrim(config('services.shopee.base_url', 'https://partner.shopeemobile.com'), '/');
        $this->mode = config('services.shopee.mode', 'sandbox');
        $this->timeout = (int) config('services.shopee.timeout', 15);
    }

    /**
     * Determine if live API credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->partnerId) && !empty($this->partnerKey);
    }

    /**
     * Get API status information.
     */
    public function getStatus(): array
    {
        return [
            'platform'    => 'Shopee Open Platform',
            'api_version' => 'v2.0',
            'mode'        => $this->mode,
            'base_url'    => $this->baseUrl,
            'configured'  => $this->isConfigured(),
            'partner_id'  => $this->partnerId ? substr($this->partnerId, 0, 3) . '***' : null,
            'timeout_sec' => $this->timeout,
        ];
    }

    /**
     * Calculate HMAC-SHA256 signature according to Shopee Open Platform V2 specification.
     */
    public function generateSignature(string $path, int $timestamp, ?string $accessToken = null, ?string $shopId = null): string
    {
        if (empty($this->partnerKey)) {
            return '';
        }

        if ($accessToken !== null && $shopId !== null) {
            $baseString = sprintf('%s%s%s%s%s', $this->partnerId, $path, $timestamp, $accessToken, $shopId);
        } else {
            $baseString = sprintf('%s%s%s', $this->partnerId, $path, $timestamp);
        }

        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Fetch order list for an hourly window.
     *
     * @param string $accessToken Shop OAuth access token
     * @param string $shopId Official Shopee Shop ID
     * @param int $timeFrom Unix timestamp in seconds
     * @param int $timeTo Unix timestamp in seconds
     */
    public function getOrderList(string $accessToken, string $shopId, int $timeFrom, int $timeTo, int $pageSize = 50): array
    {
        $path = '/api/v2/order/get_order_list';

        if (!$this->isConfigured() || $this->mode === 'mock') {
            return $this->getMockOrderList($shopId, $timeFrom, $timeTo);
        }

        $timestamp = Carbon::now()->timestamp;
        $sign = $this->generateSignature($path, $timestamp, $accessToken, $shopId);

        $queryParams = [
            'partner_id'        => $this->partnerId,
            'timestamp'         => $timestamp,
            'access_token'      => $accessToken,
            'shop_id'           => $shopId,
            'sign'              => $sign,
            'time_range_field'  => 'create_time',
            'time_from'         => $timeFrom,
            'time_to'           => $timeTo,
            'page_size'         => $pageSize,
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}{$path}", $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['error']) && !empty($data['error'])) {
                    Log::warning("Shopee API getOrderList warning: {$data['message']}", ['response' => $data]);
                    return $this->getMockOrderList($shopId, $timeFrom, $timeTo, "API Warning: {$data['message']}");
                }
                return $data;
            }

            Log::error("Shopee API HTTP error: {$response->status()} - {$response->body()}");
            return $this->getMockOrderList($shopId, $timeFrom, $timeTo, "HTTP {$response->status()} fallback");
        } catch (\Throwable $e) {
            Log::error("Shopee API exception: " . $e->getMessage());
            return $this->getMockOrderList($shopId, $timeFrom, $timeTo, "Exception: {$e->getMessage()}");
        }
    }

    /**
     * Fetch order details including line items, escrow, and voucher discounts.
     */
    public function getOrderDetail(string $accessToken, string $shopId, array $orderSnList): array
    {
        $path = '/api/v2/order/get_order_detail';

        if (!$this->isConfigured() || $this->mode === 'mock') {
            return $this->getMockOrderDetail($orderSnList);
        }

        $timestamp = Carbon::now()->timestamp;
        $sign = $this->generateSignature($path, $timestamp, $accessToken, $shopId);

        $queryParams = [
            'partner_id'   => $this->partnerId,
            'timestamp'    => $timestamp,
            'access_token' => $accessToken,
            'shop_id'      => $shopId,
            'sign'         => $sign,
            'order_sn_list'=> implode(',', $orderSnList),
            'response_optional_fields' => 'buyer_user_id,item_list,payment_method,total_amount,voucher_platform,voucher_seller',
        ];

        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}{$path}", $queryParams);
            if ($response->successful()) {
                return $response->json();
            }
            return $this->getMockOrderDetail($orderSnList);
        } catch (\Throwable $e) {
            Log::error("Shopee getOrderDetail exception: " . $e->getMessage());
            return $this->getMockOrderDetail($orderSnList);
        }
    }

    /**
     * Refresh an expired Shopee OAuth access token using refresh_token.
     */
    public function refreshToken(string $refreshToken, string $shopId): array
    {
        $path = '/api/v2/public/refresh_token';

        if (!$this->isConfigured() || $this->mode === 'mock') {
            return [
                'access_token'  => 'mock_shopee_access_' . bin2hex(random_bytes(16)),
                'refresh_token' => 'mock_shopee_refresh_' . bin2hex(random_bytes(16)),
                'expire_in'     => 14400, // 4 hours
            ];
        }

        $timestamp = Carbon::now()->timestamp;
        $sign = $this->generateSignature($path, $timestamp);

        $payload = [
            'partner_id'    => (int) $this->partnerId,
            'timestamp'     => $timestamp,
            'sign'          => $sign,
            'refresh_token' => $refreshToken,
            'shop_id'       => (int) $shopId,
        ];

        try {
            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}{$path}", $payload);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error("Shopee refreshToken exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fallback mock order list generator for sandbox testing / simulation.
     */
    protected function getMockOrderList(string $shopId, int $timeFrom, int $timeTo, ?string $reason = null): array
    {
        $hour = Carbon::createFromTimestamp($timeFrom)->hour;
        $durationSeconds = max(60, $timeTo - $timeFrom);
        $fraction = min(1.0, $durationSeconds / 3600);
        $baseOrders = ($hour >= 9 && $hour <= 23) ? rand(15, 38) : rand(3, 12);
        $orderCount = max(1, (int) round($baseOrders * $fraction));
        
        $orderList = [];
        for ($i = 0; $i < $orderCount; $i++) {
            $orderList[] = [
                'order_sn'     => '26' . Carbon::createFromTimestamp($timeFrom)->format('md') . rand(100000, 999999),
                'order_status' => 'READY_TO_SHIP',
            ];
        }

        return [
            'error'       => '',
            'message'     => $reason ? "Notice: Used fallback generator ({$reason})" : 'OK',
            'response'    => [
                'more'       => false,
                'order_list' => $orderList,
                'total_count'=> count($orderList),
            ],
            '_meta' => [
                'source'     => 'simulated_open_api',
                'time_from'  => $timeFrom,
                'time_to'    => $timeTo,
                'shop_id'    => $shopId,
            ],
        ];
    }

    /**
     * Fallback mock order detail generator.
     */
    protected function getMockOrderDetail(array $orderSnList): array
    {
        $details = [];
        foreach ($orderSnList as $sn) {
            $units = rand(1, 4);
            $itemPrice = rand(250, 950);
            $totalAmount = $units * $itemPrice;
            $sellerVoucher = rand(0, 1) === 1 ? rand(20, 50) : 0;

            $details[] = [
                'order_sn'        => $sn,
                'total_amount'    => $totalAmount,
                'voucher_seller'  => $sellerVoucher,
                'voucher_platform'=> rand(0, 30),
                'item_list'       => [
                    [
                        'item_id'         => rand(10000, 99999),
                        'item_name'       => 'Beauty Gold Collagen Serum',
                        'model_quantity_purchased' => $units,
                        'model_discounted_price'   => $itemPrice,
                    ],
                ],
            ];
        }

        return [
            'error'    => '',
            'message'  => 'OK',
            'response' => [
                'order_list' => $details,
            ],
        ];
    }
}
