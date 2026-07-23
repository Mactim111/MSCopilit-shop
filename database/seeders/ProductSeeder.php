<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([

            // APPLE
            [
                'title' => 'Смартфон Apple iPhone 16',
                'slug' => 'apple-iphone-16',
                'brand_id' => 1,
                'category_id' => 3,
                'excerpt' => 'Новый Apple iPhone 16 с улучшенной камерой и производительностью.',
                'description' => 'Подробное описание iPhone 16...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Смартфон Apple iPhone 17',
                'slug' => 'apple-iphone-17',
                'brand_id' => 1,
                'category_id' => 3,
                'excerpt' => 'Флагман Apple iPhone 17 с обновлённым дизайном.',
                'description' => 'Подробное описание iPhone 17...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Смартфон Apple iPhone 17 Pro Max',
                'slug' => 'apple-iphone-17-pro-max',
                'brand_id' => 1,
                'category_id' => 3,
                'excerpt' => 'Топовая модель Apple с лучшей камерой и автономностью.',
                'description' => 'Подробное описание iPhone 17 Pro Max...',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // SAMSUNG
            [
                'title' => 'Смартфон Samsung Galaxy S25 Ultra',
                'slug' => 'samsung-galaxy-s25-ultra',
                'brand_id' => 2,
                'category_id' => 3,
                'excerpt' => 'Флагман Samsung с мощной камерой и стилусом.',
                'description' => 'Подробное описание Galaxy S25 Ultra...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Смартфон Samsung Galaxy A57 5G',
                'slug' => 'samsung-galaxy-a57-5g',
                'brand_id' => 2,
                'category_id' => 3,
                'excerpt' => 'Доступный смартфон Samsung с поддержкой 5G.',
                'description' => 'Подробное описание Galaxy A57 5G...',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // XIAOMI
            [
                'title' => 'Смартфон Xiaomi Redmi 15C',
                'slug' => 'xiaomi-redmi-15c',
                'brand_id' => 3,
                'category_id' => 3,
                'excerpt' => 'Бюджетный смартфон Xiaomi с большим экраном.',
                'description' => 'Подробное описание Redmi 15C...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Смартфон Xiaomi Redmi Note 14',
                'slug' => 'xiaomi-redmi-note-14',
                'brand_id' => 3,
                'category_id' => 3,
                'excerpt' => 'Новая модель Redmi Note с улучшенной камерой.',
                'description' => 'Подробное описание Redmi Note 14...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Смартфон Xiaomi Redmi Note 15',
                'slug' => 'xiaomi-redmi-note-15',
                'brand_id' => 3,
                'category_id' => 3,
                'excerpt' => 'Обновлённый Redmi Note 15 с мощным процессором.',
                'description' => 'Подробное описание Redmi Note 15...',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
