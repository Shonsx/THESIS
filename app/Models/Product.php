<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductStock;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'gender',
        'price',
        'description',
        'image',
        'sizes',
    ];

    protected $casts = [
        'sizes' => 'array',
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }
}
