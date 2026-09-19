<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'report_hour',
        'total_orders',
        'total_units',
        'gross_sales',
        'discounts',
        'refunds',
        'net_sales',
        'report_data',
        'generated_at',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date:Y-m-d',
        'report_hour' => 'integer',
        'total_orders' => 'integer',
        'total_units' => 'integer',
        'gross_sales' => 'decimal:2',
        'discounts' => 'decimal:2',
        'refunds' => 'decimal:2',
        'net_sales' => 'decimal:2',
        'report_data' => 'array',
        'generated_at' => 'datetime',
    ];
}
