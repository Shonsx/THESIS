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
        'extra_images',
        'sizes',
        'measurement_image',
    ];

    protected $casts = [
        'sizes' => 'array',
        'extra_images' => 'array',
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
