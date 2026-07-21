<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('brands')->insert([
            [
                'title' => 'APPLE',
                'slug' => 'apple',
                'excerpt' => 'Apple Inc. — американская технологическая компания, производитель смартфонов, планшетов и ноутбуков.',
                'description' => 'Apple — мировой лидер в области потребительской электроники. Основные продукты: iPhone, iPad, Mac, Apple Watch.',
                'logo' => 'storage/images/brands/apple.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'SAMSUNG',
                'slug' => 'samsung',
                'excerpt' => 'Samsung — южнокорейская компания, производитель смартфонов, телевизоров и бытовой техники.',
                'description' => 'Samsung — один из крупнейших производителей электроники. Основные продукты: смартфоны Galaxy, телевизоры QLED, бытовая техника.',
                'logo' => 'storage/images/brands/samsung.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'XIAOMI',
                'slug' => 'xiaomi',
                'excerpt' => 'Xiaomi — китайская компания, производитель смартфонов, гаджетов и умной техники.',
                'description' => 'Xiaomi производит смартфоны, планшеты, умные устройства и бытовую электронику. Линейки: Redmi, Mi, Poco.',
                'logo' => 'storage/images/brands/xiaomi.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
