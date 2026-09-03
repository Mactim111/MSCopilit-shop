<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очищаем старые секции для главной, чтобы избежать дублей при тестах
        PageSection::where('page_name', 'home')->delete();

        $sections = [
            // 1. Главный баннер (сверху)
            [
                'page_name'   => 'home',
                'title'       => null,
                'type'        => 'one_banner',
                'source_type' => 'images',
                'source_value'=> 'main-banner-slider', // группа из frontend_images
                'show_tags'   => false,
                'order'       => 10,
            ],
            // 2. Слайдер подкатегорий (карточки с картинками)
            [
                'page_name'   => 'home',
                'title'       => null,
                'type'        => 'subcategory_cards',
                'source_type' => 'manual',
                'source_value'=> null,
                'show_tags'   => false,
                'order'       => 20,
            ],
            // 3. Блок "Хиты продаж" (Заголовок + Теги + Товары)
            [
                'page_name'   => 'home',
                'title'       => 'Хиты продаж',
                'type'        => 'product_slider',
                'source_type' => 'best_sellers',
                'source_value'=> null,
                'show_tags'   => true,
                'order'       => 30,
            ],
            // 4. Двойной баннер под Хитами
            [
                'page_name'   => 'home',
                'title'       => null,
                'type'        => 'double_banner',
                'source_type' => 'images',
                'source_value'=> 'banner-best-sellers',
                'show_tags'   => false,
                'order'       => 40,
            ],
            // 5. Блок "Новинки"
            [
                'page_name'   => 'home',
                'title'       => 'Новинки',
                'type'        => 'product_slider',
                'source_type' => 'new_arrivals',
                'source_value'=> null,
                'show_tags'   => true,
                'order'       => 50,
            ],
            // 6. Баннер новинок
            [
                'page_name'   => 'home',
                'title'       => null,
                'type'        => 'double_banner',
                'source_type' => 'images',
                'source_value'=> 'new-arrivals-banner',
                'show_tags'   => false,
                'order'       => 60,
            ],
            // Секции для страницы варианта товара (шаблон 'variants.show') и страницы подкатегории (шаблон 'catalog.subcategory'), 
            // и других страниц, где могут использоваться слайдеры с ПЯТЬЮ в ряд мелкими карточками вариантов товаров (содержат ТОЛЬКО! слева фото, а справа название варианта товара). 
            [
                'page_name'   => 'product_show',
                'title'       => 'Похожие товары',
                'type'        => 'product_slider',
                'source_type' => 'related', // логику пропишем в сервисе
                'show_tags'   => false,
                'order'       => 10,
            ],
            [
                'page_name'   => 'product_show',
                'title'       => 'Покупают вместе',
                'type'        => 'product_slider',
                'source_type' => 'related', // для теста тот же источник
                'show_tags'   => true,
                'order'       => 20,
            ],
            [
                'page_name'   => 'product_show',
                'title'       => 'Ранее вы смотрели',
                'type'        => 'recently_viewed', // новый тип
                'source_type' => 'manual',
                'show_tags'   => false,
                'order'       => 30,
            ],
        ];

        foreach ($sections as $section) {
            PageSection::create($section);
        }
    }
}
