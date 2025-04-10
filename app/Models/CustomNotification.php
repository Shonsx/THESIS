<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CustomNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'message',
        'order_id',
        'data',
        'read_at',
    ];

    public $incrementing = false; // UUIDs are not auto-incrementing
    protected $keyType = 'string'; // UUIDs are stored as strings

    protected static function boot()
    {
        parent::boot();

        // Automatically generate a UUID when creating a notification
        static::creating(function ($notification) {
            $notification->id = (string) Str::uuid();
        });
    }
}
