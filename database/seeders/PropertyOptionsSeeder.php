<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\PropertyOption;

// class PropertyOptionsSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         $options = [

//             // 1. Линейка
//             ['property_id' => 1, 'value' => 'iPhone 16',              'slug' => 'iphone-16'],
//             ['property_id' => 1, 'value' => 'iPhone 17',              'slug' => 'iphone-17'],
//             ['property_id' => 1, 'value' => 'iPhone 17 Pro Max',      'slug' => 'iphone-17-pro-max'],
//             ['property_id' => 1, 'value' => 'Samsung Galaxy S25 Ultra','slug' => 'samsung-s25-ultra'],
//             ['property_id' => 1, 'value' => 'Samsung Galaxy A57 5G',  'slug' => 'samsung-a57-5g'],
//             ['property_id' => 1, 'value' => 'Xiaomi Redmi 15C',       'slug' => 'xiaomi-redmi-15c'],
//             ['property_id' => 1, 'value' => 'Xiaomi Redmi Note 14',   'slug' => 'xiaomi-redmi-note-14'],
//             ['property_id' => 1, 'value' => 'Xiaomi Redmi Note 15',   'slug' => 'xiaomi-redmi-note-15'],

//             // 2. Объем встроенной памяти
//             ['property_id' => 2, 'value' => '128 ГБ', 'slug' => '128gb'],
//             ['property_id' => 2, 'value' => '256 ГБ', 'slug' => '256gb'],
//             ['property_id' => 2, 'value' => '512 ГБ', 'slug' => '512gb'],
//             ['property_id' => 2, 'value' => '1 ТБ',   'slug' => '1tb'],

//             // 3. Объем оперативной памяти
//             ['property_id' => 3, 'value' => '4 ГБ',  'slug' => '4gb'],
//             ['property_id' => 3, 'value' => '6 ГБ',  'slug' => '6gb'],
//             ['property_id' => 3, 'value' => '8 ГБ',  'slug' => '8gb'],
//             ['property_id' => 3, 'value' => '12 ГБ', 'slug' => '12gb'],

//             // 4. Диагональ экрана (range)
//             ['property_id' => 4, 'value' => '6.1', 'slug' => '6-1', 'numeric_value' => 6.10],
//             ['property_id' => 4, 'value' => '6.3', 'slug' => '6-3', 'numeric_value' => 6.30],
//             ['property_id' => 4, 'value' => '6.67', 'slug' => '6-67', 'numeric_value' => 6.67],
//             ['property_id' => 4, 'value' => '6.7', 'slug' => '6-7', 'numeric_value' => 6.70],
//             ['property_id' => 4, 'value' => '6.77', 'slug' => '6-77', 'numeric_value' => 6.77],
//             ['property_id' => 4, 'value' => '6.9', 'slug' => '6-9', 'numeric_value' => 6.90],

//             // 5. NFC (toggle)
//             ['property_id' => 5, 'value' => 'Есть NFC', 'slug' => 'yes'],
//             ['property_id' => 5, 'value' => 'Нет NFC',  'slug' => 'no'],

//             // 6. Цвет корпуса
//             ['property_id' => 6, 'value' => 'Черный', 'slug' => 'chernyy', 'color_hex' => '#1a1a1a'],
//             ['property_id' => 6, 'value' => 'Белый',  'slug' => 'belyy', 'color_hex' => '#ffffff'],
//             ['property_id' => 6, 'value' => 'Синий',  'slug' => 'siniy',  'color_hex' => '#4169e1'],
//             ['property_id' => 6, 'value' => 'Бирюзовый', 'slug' => 'biryuzovyy', 'color_hex' => '#40E0D0'],
//             ['property_id' => 6, 'value' => 'Розовый',  'slug' => 'rozovyy', 'color_hex' => '#FFC0CB'],
//             ['property_id' => 6, 'value' => 'Сиреневый', 'slug' => 'sirenevyy', 'color_hex' => '#C8A2C8'],
//             ['property_id' => 6, 'value' => 'Зеленый',  'slug' => 'zelenyy',  'color_hex' => '#008000'],
//             ['property_id' => 6, 'value' => 'Серебристый', 'slug' => 'serebristyy', 'color_hex' => '#C0C0C0'],
//             ['property_id' => 6, 'value' => 'Оранжевый',  'slug' => 'oranzhevyy', 'color_hex' => '#FFA500'],
//             ['property_id' => 6, 'value' => 'Серый',  'slug' => 'seryy',  'color_hex' => '#808080'],
//             ['property_id' => 6, 'value' => 'Фиолетовый',  'slug' => 'fioletovyy', 'color_hex' => '#800080'],
//             ['property_id' => 6, 'value' => 'Голубой',  'slug' => 'goluboy',  'color_hex' => '#00BFFF'],

//             // 7. Емкость аккумулятора (range)
//             ['property_id' => 7, 'value' => '3561 мА·ч', 'slug' => '3561', 'numeric_value' => 3561],
//             ['property_id' => 7, 'value' => '3692 мА·ч', 'slug' => '3692', 'numeric_value' => 3692],
//             ['property_id' => 7, 'value' => '5088 мА·ч', 'slug' => '5088', 'numeric_value' => 5000],
//             ['property_id' => 7, 'value' => '5000 мА·ч', 'slug' => '5000', 'numeric_value' => 5088],
//             ['property_id' => 7, 'value' => '5500 мА·ч', 'slug' => '5500', 'numeric_value' => 5500],
//             ['property_id' => 7, 'value' => '6000 мА·ч', 'slug' => '6000', 'numeric_value' => 6000],
//             ['property_id' => 7, 'value' => '7000 мА·ч', 'slug' => '7000', 'numeric_value' => 7000],

//             // 8. Беспроводная зарядка (toggle)
//             ['property_id' => 8, 'value' => 'Поддерживает', 'slug' => 'yes'],
//             ['property_id' => 8, 'value' => 'Не поддерживает', 'slug' => 'no'],
//         ];

//         foreach ($options as $opt) {
//             PropertyOption::updateOrCreate(
//                 ['slug' => $opt['slug']],
//                 $opt
//             );
//         }
//     }
// }

class PropertyOptionsSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Мы больше НЕ используем property_id напрямую.
         * Вместо этого — property_slug, чтобы сидер был стабильным.
         */
        $options = [

            // 1. Линейка
            ['property_slug' => 'lineup', 'value' => 'iPhone 16',               'slug' => 'iphone-16'],
            ['property_slug' => 'lineup', 'value' => 'iPhone 17',               'slug' => 'iphone-17'],
            ['property_slug' => 'lineup', 'value' => 'iPhone 17 Pro Max',       'slug' => 'iphone-17-pro-max'],
            ['property_slug' => 'lineup', 'value' => 'Samsung Galaxy S25 Ultra','slug' => 'samsung-s25-ultra'],
            ['property_slug' => 'lineup', 'value' => 'Samsung Galaxy A57 5G',   'slug' => 'samsung-a57-5g'],
            ['property_slug' => 'lineup', 'value' => 'Xiaomi Redmi 15C',        'slug' => 'xiaomi-redmi-15c'],
            ['property_slug' => 'lineup', 'value' => 'Xiaomi Redmi Note 14',    'slug' => 'xiaomi-redmi-note-14'],
            ['property_slug' => 'lineup', 'value' => 'Xiaomi Redmi Note 15',    'slug' => 'xiaomi-redmi-note-15'],

            // 2. Объем встроенной памяти
            ['property_slug' => 'built_in_memory', 'value' => '128 ГБ', 'slug' => '128gb'],
            ['property_slug' => 'built_in_memory', 'value' => '256 ГБ', 'slug' => '256gb'],
            ['property_slug' => 'built_in_memory', 'value' => '512 ГБ', 'slug' => '512gb'],
            ['property_slug' => 'built_in_memory', 'value' => '1 ТБ',   'slug' => '1tb'],

            // 3. Оперативная память
            ['property_slug' => 'ram', 'value' => '4 ГБ',  'slug' => '4gb'],
            ['property_slug' => 'ram', 'value' => '6 ГБ',  'slug' => '6gb'],
            ['property_slug' => 'ram', 'value' => '8 ГБ',  'slug' => '8gb'],
            ['property_slug' => 'ram', 'value' => '12 ГБ', 'slug' => '12gb'],

            // 4. Диагональ экрана (range)
            ['property_slug' => 'screen_size', 'value' => '6.1', 'slug' => '6-1', 'numeric_value' => 6.10],
            ['property_slug' => 'screen_size', 'value' => '6.3', 'slug' => '6-3', 'numeric_value' => 6.30],
            ['property_slug' => 'screen_size', 'value' => '6.67','slug' => '6-67','numeric_value' => 6.67],
            ['property_slug' => 'screen_size', 'value' => '6.7', 'slug' => '6-7', 'numeric_value' => 6.70],
            ['property_slug' => 'screen_size', 'value' => '6.77','slug' => '6-77','numeric_value' => 6.77],
            ['property_slug' => 'screen_size', 'value' => '6.9', 'slug' => '6-9', 'numeric_value' => 6.90],

            // 5. NFC (toggle)
            // ['property_slug' => 'nfc', 'slug' => 'yes'],
            // ['property_slug' => 'nfc', 'slug' => 'no'],
            ['property_slug' => 'nfc', 'value' => 'Есть NFC', 'slug' => 'yes'],
            ['property_slug' => 'nfc', 'value' => 'Нет NFC',  'slug' => 'no'],

            // 6. Цвет корпуса
            ['property_slug' => 'case_color', 'value' => 'Черный', 'slug' => 'chernyy', 'color_hex' => '#1a1a1a'],
            ['property_slug' => 'case_color', 'value' => 'Белый',  'slug' => 'belyy',   'color_hex' => '#ffffff'],
            ['property_slug' => 'case_color', 'value' => 'Синий',  'slug' => 'siniy',   'color_hex' => '#4169e1'],
            ['property_slug' => 'case_color', 'value' => 'Бирюзовый', 'slug' => 'biryuzovyy', 'color_hex' => '#40E0D0'],
            ['property_slug' => 'case_color', 'value' => 'Розовый', 'slug' => 'rozovyy', 'color_hex' => '#FFC0CB'],
            ['property_slug' => 'case_color', 'value' => 'Сиреневый', 'slug' => 'sirenevyy', 'color_hex' => '#C8A2C8'],
            ['property_slug' => 'case_color', 'value' => 'Зеленый', 'slug' => 'zelenyy', 'color_hex' => '#008000'],
            ['property_slug' => 'case_color', 'value' => 'Серебристый', 'slug' => 'serebristyy', 'color_hex' => '#C0C0C0'],
            ['property_slug' => 'case_color', 'value' => 'Оранжевый', 'slug' => 'oranzhevyy', 'color_hex' => '#FFA500'],
            ['property_slug' => 'case_color', 'value' => 'Серый', 'slug' => 'seryy', 'color_hex' => '#808080'],
            ['property_slug' => 'case_color', 'value' => 'Фиолетовый', 'slug' => 'fioletovyy', 'color_hex' => '#800080'],
            ['property_slug' => 'case_color', 'value' => 'Голубой', 'slug' => 'goluboy', 'color_hex' => '#00BFFF'],

            // 7. Емкость аккумулятора (range)
            ['property_slug' => 'battery_capacity', 'value' => '3561 мА·ч', 'slug' => '3561', 'numeric_value' => 3561],
            ['property_slug' => 'battery_capacity', 'value' => '3692 мА·ч', 'slug' => '3692', 'numeric_value' => 3692],
            ['property_slug' => 'battery_capacity', 'value' => '5088 мА·ч', 'slug' => '5088', 'numeric_value' => 5088],
            ['property_slug' => 'battery_capacity', 'value' => '5000 мА·ч', 'slug' => '5000', 'numeric_value' => 5000],
            ['property_slug' => 'battery_capacity', 'value' => '5500 мА·ч', 'slug' => '5500', 'numeric_value' => 5500],
            ['property_slug' => 'battery_capacity', 'value' => '6000 мА·ч', 'slug' => '6000', 'numeric_value' => 6000],
            // ['property_slug' => 'battery_capacity', 'value' => '7000 мА·ч', 'slug' => '7000', 'numeric_value' => 7000],

            // 8. Беспроводная зарядка (toggle)
            // ['property_slug' => 'w_charg_sup', 'slug' => 'yes'],
            // ['property_slug' => 'w_charg_sup', 'slug' => 'no'],
            ['property_slug' => 'w_charg_sup', 'value' => 'Поддерживает', 'slug' => 'yes'],
            ['property_slug' => 'w_charg_sup', 'value' => 'Не поддерживает', 'slug' => 'no'],
        ];

        foreach ($options as $opt) {

            $propertyId = Property::where('slug', $opt['property_slug'])->value('id');

            PropertyOption::updateOrCreate(
                [
                    'slug' => $opt['slug'],
                    'property_id' => $propertyId,
                ],
                [
                    'value'         => $opt['value'] ?? null,
                    'numeric_value' => $opt['numeric_value'] ?? null,
                    'color_hex'     => $opt['color_hex'] ?? null,
                ]
            );
        }
    }
}
