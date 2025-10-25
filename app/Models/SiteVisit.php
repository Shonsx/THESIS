<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visited_at',
        'user_id',
        'ip_address',
        'user_agent',
        'last_seen',
    ];

    protected $casts = [
        'visited_at' => 'date',
        'last_seen' => 'datetime',
    ];
}