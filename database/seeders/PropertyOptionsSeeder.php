<?php

namespace Database\Seeders;

use App\Models\PropertyOption;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [

            // 1. Линейка
            ['property_id' => 1, 'value' => 'iPhone 16',              'slug' => 'iphone-16'],
            ['property_id' => 1, 'value' => 'iPhone 17',              'slug' => 'iphone-17'],
            ['property_id' => 1, 'value' => 'iPhone 17 Pro Max',      'slug' => 'iphone-17-pro-max'],
            ['property_id' => 1, 'value' => 'Samsung Galaxy S25 Ultra','slug' => 's25-ultra'],
            ['property_id' => 1, 'value' => 'Samsung Galaxy A57 5G',  'slug' => 'a57-5g'],
            ['property_id' => 1, 'value' => 'Xiaomi Redmi 15C',       'slug' => 'redmi-15c'],
            ['property_id' => 1, 'value' => 'Xiaomi Redmi Note 14',   'slug' => 'redmi-note-14'],
            ['property_id' => 1, 'value' => 'Xiaomi Redmi Note 15',   'slug' => 'redmi-note-15'],

            // 2. Объем встроенной памяти
            ['property_id' => 2, 'value' => '128 ГБ', 'slug' => '128gb'],
            ['property_id' => 2, 'value' => '256 ГБ', 'slug' => '256gb'],
            ['property_id' => 2, 'value' => '512 ГБ', 'slug' => '512gb'],
            ['property_id' => 2, 'value' => '1 ТБ',   'slug' => '1tb'],

            // 3. Объем оперативной памяти
            ['property_id' => 3, 'value' => '4 ГБ',  'slug' => '4gb'],
            ['property_id' => 3, 'value' => '6 ГБ',  'slug' => '6gb'],
            ['property_id' => 3, 'value' => '8 ГБ',  'slug' => '8gb'],
            ['property_id' => 3, 'value' => '12 ГБ', 'slug' => '12gb'],

            // 4. Диагональ экрана (range)
            ['property_id' => 4, 'value' => '6.1"', 'slug' => '6-1', 'numeric_value' => 6.10],
            ['property_id' => 4, 'value' => '6.3"', 'slug' => '6-3', 'numeric_value' => 6.30],
            ['property_id' => 4, 'value' => '6.67"', 'slug' => '6-67', 'numeric_value' => 6.67],
            ['property_id' => 4, 'value' => '6.7"', 'slug' => '6-7', 'numeric_value' => 6.70],
            ['property_id' => 4, 'value' => '6.77"', 'slug' => '6-77', 'numeric_value' => 6.77],
            ['property_id' => 4, 'value' => '6.9"', 'slug' => '6-9', 'numeric_value' => 6.90],

            // 5. NFC (toggle)
            ['property_id' => 5, 'value' => 'Есть NFC', 'slug' => 'yes'],
            ['property_id' => 5, 'value' => 'Нет NFC',  'slug' => 'no'],

            // 6. Цвет корпуса
            ['property_id' => 6, 'value' => 'Черный', 'slug' => 'black', 'color_hex' => '#1a1a1a'],
            ['property_id' => 6, 'value' => 'Белый',  'slug' => 'white', 'color_hex' => '#ffffff'],
            ['property_id' => 6, 'value' => 'Синий',  'slug' => 'blue',  'color_hex' => '#4169e1'],

            // 7. Емкость аккумулятора (range)
            ['property_id' => 7, 'value' => '4000 мА·ч', 'slug' => '4000', 'numeric_value' => 4000],
            ['property_id' => 7, 'value' => '4500 мА·ч', 'slug' => '4500', 'numeric_value' => 4500],
            ['property_id' => 7, 'value' => '5000 мА·ч', 'slug' => '5000', 'numeric_value' => 5000],

            // 8. Беспроводная зарядка (toggle)
            ['property_id' => 8, 'value' => 'Поддерживает', 'slug' => 'yes'],
            ['property_id' => 8, 'value' => 'Не поддерживает', 'slug' => 'no'],
        ];

        foreach ($options as $opt) {
            PropertyOption::updateOrCreate(
                ['slug' => $opt['slug']],
                $opt
            );
        }
    }
}
