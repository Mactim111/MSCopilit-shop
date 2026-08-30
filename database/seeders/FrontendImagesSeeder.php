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
                'group' => 'main-banner-slider',
                'title' => 'Главный баннер №1',
                'path' => 'storage/frontend/sliders/main-banner-slider/1.jpg',
                'order' => 1,
                'active' => true,
            ],
            [
                'group' => 'main-banner-slider',
                'title' => 'Главный баннер №2',
                'path' => 'storage/frontend/sliders/main-banner-slider/2.jpg',
                'order' => 2,
                'active' => true,
            ],
            [
                'group' => 'main-banner-slider',
                'title' => 'Главный баннер №3',
                'path' => 'storage/frontend/sliders/main-banner-slider/3.jpg',
                'order' => 3,
                'active' => true,
            ],
            [
                'group' => 'main-banner-slider',
                'title' => 'Главный баннер №4',
                'path' => 'storage/frontend/sliders/main-banner-slider/4.jpg',
                'order' => 4,
                'active' => true,
            ],
            [
                'group' => 'main-banner-slider',
                'title' => 'Главный баннер №5',
                'path' => 'storage/frontend/sliders/main-banner-slider/5.jpg',
                'order' => 5,
                'active' => true,
            ],

            [
                'group' => 'banner-top',
                'title' => 'Верхний баннер (бонусы) №1',
                'path' => 'storage/frontend/sliders/banner-top/1.jpg',
                'order' => 1,
                'active' => true,
            ],


            [
                'group' => 'banner-best-sellers',
                'title' => 'Баннер в зоне хитов продаж №1',
                'path' => 'storage/frontend/sliders/banner-best-sellers/1.jpg',
                'order' => 1,
                'active' => true,
            ],
            [
                'group' => 'banner-best-sellers',
                'title' => 'Баннер в зоне хитов продаж №2',
                'path' => 'storage/frontend/sliders/banner-best-sellers/2.jpg',
                'order' => 2,
                'active' => true,
            ],
            [
                'group' => 'banner-best-sellers',
                'title' => 'Баннер в зоне хитов продаж №3',
                'path' => 'storage/frontend/sliders/banner-best-sellers/3.jpg',
                'order' => 3,
                'active' => true,
            ],
            [
                'group' => 'banner-best-sellers',
                'title' => 'Баннер в зоне хитов продаж №4',
                'path' => 'storage/frontend/sliders/banner-best-sellers/4.jpg',
                'order' => 4,
                'active' => true,
            ],
            [
                'group' => 'banner-best-sellers',
                'title' => 'Баннер в зоне хитов продаж №5',
                'path' => 'storage/frontend/sliders/banner-best-sellers/5.jpg',
                'order' => 5,
                'active' => true,
            ],
            [
                'group' => 'banner-best-sellers',
                'title' => 'Баннер в зоне хитов продаж №6',
                'path' => 'storage/frontend/sliders/banner-best-sellers/6.jpg',
                'order' => 6,
                'active' => true,
            ],


            [
                'group' => 'new-arrivals-banner',
                'title' => 'Баннер новинок №1',
                'path' => 'storage/frontend/sliders/new-arrivals-banner/1.jpg',
                'order' => 1,
                'active' => true,
            ],
            [
                'group' => 'new-arrivals-banner',
                'title' => 'Баннер новинок №2',
                'path' => 'storage/frontend/sliders/new-arrivals-banner/2.jpg',
                'order' => 2,
                'active' => true,
            ],
            [
                'group' => 'new-arrivals-banner',
                'title' => 'Баннер новинок №3',
                'path' => 'storage/frontend/sliders/new-arrivals-banner/3.jpg',
                'order' => 3,
                'active' => true,
            ],
            [
                'group' => 'new-arrivals-banner',
                'title' => 'Баннер новинок №4',
                'path' => 'storage/frontend/sliders/new-arrivals-banner/4.jpg',
                'order' => 4,
                'active' => true,
            ],
            [
                'group' => 'new-arrivals-banner',
                'title' => 'Баннер новинок №5',
                'path' => 'storage/frontend/sliders/new-arrivals-banner/5.jpg',
                'order' => 5,
                'active' => true,
            ],
            [
                'group' => 'new-arrivals-banner',
                'title' => 'Баннер новинок №6',
                'path' => 'storage/frontend/sliders/new-arrivals-banner/6.jpg',
                'order' => 6,
                'active' => true,
            ],


        ]);

    }
}
