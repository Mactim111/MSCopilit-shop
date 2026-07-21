<?php

namespace Database\Seeders;

use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()
            ->count(100)
            ->create()
            ->each(function ($product) {

                // Количество изображений в галерее
                $count = rand(3, 5);

                for ($i = 0; $i < $count; $i++) {
                    ProductImage::factory()->create([
                        'product_id' => $product->id,
                        'position' => $i,
                    ]);
                }

                // Генерируем 2–4 варианта товара
                $variantCount = rand(2, 4);

                $colors = ['Black', 'White', 'Red', 'Blue'];
                $sizes = ['S', 'M', 'L', 'XL'];

                for ($i = 0; $i < $variantCount; $i++) {

                    $variant = $product->variants()->create(
                        ProductVariant::factory()->make()->toArray()
                    );

                    // Добавляем цвет
                    $variant->options()->create([
                        'name' => 'color',
                        'value' => $colors[array_rand($colors)],
                    ]);

                    // Добавляем размер
                    $variant->options()->create([
                        'name' => 'size',
                        'value' => $sizes[array_rand($sizes)],
                    ]);
                }
            });
    }
}
