<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        // Смартфоны (id = 6) → товары 1–8
        for ($i = 1; $i <= 8; $i++) {
            DB::table('category_product')->insert([
                'category_id' => 6,
                'product_id' => $i,
            ]);
        }
    }

    /*
|--------------------------------------------------------------------------
| Динамический вариант CategoryProductSeeder (рекомендуемый)
|--------------------------------------------------------------------------
| Этот код не использует жёсткие ID категорий.
| Он автоматически получает ID по slug, что делает сидер
| полностью независимым от порядка вставки категорий.
*/


    // public function run(): void
    // {
    //     // Получаем ID категории "Смартфоны" по slug
    //     $smartphonesId = DB::table('categories')
    //         ->where('slug', 'smartfony')
    //         ->value('id');

    //     // Привязываем товары 1–8 к категории "Смартфоны"
    //     for ($i = 1; $i <= 8; $i++) {
    //         DB::table('category_product')->insert([
    //             'category_id' => $smartphonesId,
    //             'product_id' => $i,
    //         ]);
    //     }

        /*
        // Пример для "Элементы питания", если появятся товары:
        $powerId = DB::table('categories')
            ->where('slug', 'elementy-pitaniya')
            ->value('id');

        $tvAccessoriesId = DB::table('categories')
            ->where('slug', 'televizory-i-aksessuary')
            ->value('id');

        // Пример привязки товара с id = 10 к двум категориям:
        DB::table('category_product')->insert([
            ['category_id' => $powerId, 'product_id' => 10],
            ['category_id' => $tvAccessoriesId, 'product_id' => 10],
        ]);
        */
    // }
}


