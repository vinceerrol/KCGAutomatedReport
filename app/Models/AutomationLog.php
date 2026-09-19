<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'job',
        'status',
        'message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
