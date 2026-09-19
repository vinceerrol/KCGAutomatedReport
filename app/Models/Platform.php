<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    /**
     * Get the shops that belong to the platform.
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * Get all hourly metrics for the platform through shops.
     */
    public function hourlyMetrics(): HasManyThrough
    {
        return $this->hasManyThrough(HourlyMetric::class, Shop::class);
    }
}
