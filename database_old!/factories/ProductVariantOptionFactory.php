<?php

namespace Database\Factories;

use App\Models\ProductVariantOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariantOption>
 */
class ProductVariantOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'color', // будет переопределяться
            'value' => 'Black', // будет переопределяться
        ];
    }
}
