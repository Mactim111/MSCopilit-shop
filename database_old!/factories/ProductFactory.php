<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
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
        $title = $this->faker->unique()->sentence(3);
        $price = $this->faker->randomFloat(2, 50, 1000);

        return [
            
            // закомментили строку ниже, так как уже это поле НЕ! НУЖНО! для нового сидера и фабрики категорий с вложенностью категорий
            // 'category_id' => rand(1, 10),

            'title' => $title,
            'slug' => Str::slug($title),

            'excerpt' => $this->faker->paragraph(2),
            'description' => $this->faker->paragraphs(3, true),

            'price' => $price,
            'old_price' => $this->faker->randomElement([0, $price * 1.1]),

            'stock' => rand(0, 50),

            // Лейблы: hit, new, sale
            'labels' => $this->faker->randomElement([
                null,
                ['hit'],
                ['new'],
                ['sale'],
                ['hit', 'sale'],
                ['new', 'sale'],
            ]),

            // меняем ниже на другой источник изображений
            // 'image' => 'https://picsum.photos/seed/' . $this->faker->uuid . '/400/400',
            'image' => 'https://loremflickr.com/400/300/category?lock=' . $this->faker->unique()->numberBetween(1, 999999),

            // Главная картинка
//            'image' => 'assets/img/products/' . rand(1, 8) . '.jpg',

            // Мини‑галерея (3–5 изображений)
//            'gallery' => json_encode(
//                collect(range(1, rand(3, 5)))
//                    ->map(fn() => 'assets/img/products/' . rand(1, 8) . '.jpg')
//                    ->toArray()
//            ),
        ];
    }
}
