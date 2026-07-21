<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            // 'path' => 'https://picsum.photos/seed/' . $this->faker->uuid . '/400/400',
            // 'position' => 0, // будет переопределяться в сидере, ниже меняем источник изображений
            'path' => 'https://loremflickr.com/400/400/product?lock=' . $this->faker->unique()->numberBetween(1, 999999),
            'position' => 0
        ];
    }
}
