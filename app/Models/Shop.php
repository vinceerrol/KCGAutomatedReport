<?php

namespace App\Models;

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
}
