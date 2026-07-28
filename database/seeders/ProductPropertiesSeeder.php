<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class ProductPropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Маппинг:
         * product_slug => [
         *      property_slug => [
         *          'used_for_variant_card' => bool,
         *          'position_in_variant_card' => int
         *      ]
         * ]
         */

        $map = [

            // iPhone 16
            'apple-iphone-16' => [
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
            ],

            // iPhone 17
            'apple-iphone-17' => [
                'lineup' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 3],
            ],

            // iPhone 17 Pro Max
            'apple-iphone-17-pro-max' => [
                'lineup' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 3],
            ],

            // Samsung Galaxy S25 Ultra
            'samsung-galaxy-s25-ultra' => [
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
            ],

            // Samsung Galaxy A57 5G
            'samsung-galaxy-a57-5g' => [
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
                'built_in_memory' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 3],
            ],

            // Xiaomi Redmi 15C
            'xiaomi-redmi-15c' => [
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
                'built_in_memory' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 3],
            ],

            // Смартфон Xiaomi Redmi Note 14
            'xiaomi-redmi-note-14' => [
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
                'built_in_memory' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 3],
            ],

            // Смартфон Xiaomi Redmi Note 15
            'xiaomi-redmi-note-15' => [
                'case_color' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 1],
                'ram' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 2],
                'built_in_memory' => ['used_for_variant_card' => 1, 'position_in_variant_card' => 3],
            ],
        ];

        foreach ($map as $productSlug => $properties) {

            $product = Product::where('slug', $productSlug)->first();
            if (!$product) continue;

            foreach ($properties as $propertySlug => $settings) {

                $property = Property::where('slug', $propertySlug)->first();
                if (!$property) continue;

                DB::table('product_properties')->updateOrInsert(
                    [
                        'product_id' => $product->id,
                        'property_id' => $property->id,
                    ],
                    [
                        'used_for_variant_card' => $settings['used_for_variant_card'],
                        'position_in_variant_card' => $settings['position_in_variant_card'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
