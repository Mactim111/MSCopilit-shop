<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = [
            [
                'title' => 'Линейка',
                'slug'  => 'lineup',
                'type'  => 'checkbox',
                'used_for_filters' => true,
                'position_in_filters' => 1,
            ],
            [
                'title' => 'Объем встроенной памяти',
                'slug'  => 'built_in_memory',
                'type'  => 'checkbox',
                'used_for_filters' => true,
                'position_in_filters' => 2,
            ],
            [
                'title' => 'Объем оперативной памяти',
                'slug'  => 'ram',
                'type'  => 'checkbox',
                'used_for_filters' => true,
                'position_in_filters' => 3,
            ],
            [
                'title' => 'Диагональ экрана',
                'slug'  => 'screen_size',
                'type'  => 'range',
                'used_for_filters' => true,
                'position_in_filters' => 4,
            ],
            [
                'title' => 'NFC',
                'slug'  => 'nfc',
                'type'  => 'toggle',
                'used_for_filters' => true,
                'position_in_filters' => 5,
            ],
            [
                'title' => 'Цвет корпуса',
                'slug'  => 'case_color',
                'type'  => 'checkbox',
                'used_for_filters' => true,
                'position_in_filters' => 6,
            ],
            [
                'title' => 'Емкость аккумулятора, мА·ч',
                'slug'  => 'battery_capacity',
                'type'  => 'range',
                'used_for_filters' => true,
                'position_in_filters' => 7,
            ],
            [
                'title' => 'Поддержка беспроводной зарядки',
                'slug'  => 'w_charg_sup',
                'type'  => 'toggle',
                'used_for_filters' => true,
                'position_in_filters' => 8,
            ],
        ];

        foreach ($properties as $property) {
            Property::updateOrCreate(
                ['slug' => $property['slug']],
                $property
            );
        }
    }
}
