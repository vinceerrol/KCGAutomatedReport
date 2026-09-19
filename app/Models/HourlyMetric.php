<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourlyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'report_date',
        'hour',
        'orders',
        'units_sold',
        'ad_spend',
        'gross_sales',
        'discounts',
        'refunds',
        'net_sales',
    ];

    protected $casts = [
        'report_date' => 'date:Y-m-d',
        'hour' => 'integer',
        'orders' => 'integer',
        'units_sold' => 'integer',
        'ad_spend' => 'decimal:2',
        'gross_sales' => 'decimal:2',
        'discounts' => 'decimal:2',
        'refunds' => 'decimal:2',
        'net_sales' => 'decimal:2',
    ];

    /**
     * Calculate ROAS (Return on Ad Spend = gross_sales / ad_spend)
     */
    public function getRoasAttribute(): float
    {
        $adSpend = (float) $this->ad_spend;
        $sales = (float) $this->gross_sales;

        return $adSpend > 0 ? round($sales / $adSpend, 2) : 0.00;
    }

    /**
     * Get the shop that owns the metric.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
