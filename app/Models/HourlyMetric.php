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
        'gross_sales' => 'decimal:2',
        'discounts' => 'decimal:2',
        'refunds' => 'decimal:2',
        'net_sales' => 'decimal:2',
    ];

    /**
     * Get the shop that owns the metric.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
