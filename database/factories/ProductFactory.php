<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=> $this->faker->word,
            'price'=> $this->faker->randomFloat(2,10,100),
            'description'=> $this->faker->sentence,
            'image'=> $this->faker->imageUrl('https://m.media-amazon.com/images/I/71df+XLsJvL._SX679_.jpg'),
            'sizes' => json_encode(['S','M','L','XL']),
        ];
    }
}
