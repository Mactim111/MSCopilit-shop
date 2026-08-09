<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrontendImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('frontend_images')->insert([
            [
                'group' => 'banner-slider',
                'title' => 'Главный баннер №1',
                'path' => 'storage/slider/1.jpg',
                'order' => 1,
                'active' => true,
            ],
            [
                'group' => 'banner-slider',
                'title' => 'Главный баннер №2',
                'path' => 'storage/slider/2.jpg',
                'order' => 2,
                'active' => true,
            ],
            [
                'group' => 'banner-slider',
                'title' => 'Главный баннер №3',
                'path' => 'storage/slider/3.jpg',
                'order' => 3,
                'active' => true,
            ],
            [
                'group' => 'banner-slider',
                'title' => 'Главный баннер №4',
                'path' => 'storage/slider/4.jpg',
                'order' => 4,
                'active' => true,
            ],
            [
                'group' => 'banner-slider',
                'title' => 'Главный баннер №5',
                'path' => 'storage/slider/5.jpg',
                'order' => 5,
                'active' => true,
            ],

            [
                'group' => 'banner-top',
                'title' => 'Верхний баннер — бонусы',
                'path' => 'storage/slider/11.jpg',
                'order' => 1,
                'active' => true,
            ],
        ]);

    }
}
