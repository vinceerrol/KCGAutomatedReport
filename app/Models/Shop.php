<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_id',
        'name',
        'code',
        'status',
        'external_shop_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'extra_credentials',
    ];

    protected $casts = [
        'extra_credentials' => 'array',
        'token_expires_at'  => 'datetime',
    ];

    /**
     * Get the platform that owns the shop.
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * Get the hourly metrics for the shop.
     */
    public function hourlyMetrics(): HasMany
    {
        return $this->hasMany(HourlyMetric::class);
    }

    /**
     * Check if the API OAuth token has expired or is expiring soon (within 5 minutes).
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }

        return Carbon::now()->addMinutes(5)->greaterThanOrEqualTo($this->token_expires_at);
    }

    /**
     * Check if the shop has credentials configured for live API calls.
     */
    public function hasValidApiCredentials(): bool
    {
        return !empty($this->access_token) && !empty($this->external_shop_id);
    }
}
