<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'SKU-' . strtoupper(Str::random(10)),
            'price' => $this->faker->randomFloat(2, 50, 1000),
            'stock' => rand(0, 50),
            'image' => 'https://picsum.photos/seed/' . $this->faker->uuid . '/600/600',
        ];
    }
}
