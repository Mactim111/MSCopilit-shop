<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // старый сидер
        // Category::factory()->count(10)->create();
        // новый сидер с вложенностью категорий
        // -------------------------------
        // 1. Ручной пункт "Акции"
        Category::create([
            'title' => 'Акции',
            'slug' => 'sales',
            'excerpt' => 'Лучшие предложения и скидки',
            'image' => 'storage/assets/img/actions.png',
            'parent_id' => null,
        ]);

        // 2. 10 основных групп категорий
        $groups = Category::factory()
            ->count(10)
            ->create([
                'parent_id' => null,
            ]);

        foreach ($groups as $group) {

            // 3. 5 подкатегорий (без excerpt и image)
            $subcategories = Category::factory()
                ->count(5)
                ->child($group->id)
                ->create();

            foreach ($subcategories as $sub) {

                // 4. 4–11 под‑подкатегорий (с изображениями)
                $childCategories = Category::factory()
                    ->count(rand(4, 11))
                    ->create([
                        'parent_id' => $sub->id,
                    ]);

                foreach ($childCategories as $child) {

                    // 5. 3–10 товаров в каждой конечной категории
                    $products = Product::factory()
                        ->count(rand(3, 10))
                        ->create([
                            'category_id' => $child->id,
                        ]);

                    foreach ($products as $product) {

                        // 6. Галерея 3–5 изображений
                        for ($i = 0; $i < rand(3, 5); $i++) {
                            ProductImage::factory()->create([
                                'product_id' => $product->id,
                                'position' => $i,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
