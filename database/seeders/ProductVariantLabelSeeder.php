<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariantLabelSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем все ID вариантов товаров
        $variantIds = DB::table('product_variants')->pluck('id')->toArray();

        // Перемешиваем массив ID
        shuffle($variantIds);

        // Берём примерно треть от общего количества
        $count = floor(count($variantIds) / 3);
        $selectedVariantIds = array_slice($variantIds, 0, $count);

        // Получаем все ID лейблов
        $labelIds = DB::table('labels')->pluck('id')->toArray();

        $insertData = [];

        // Равномерное распределение лейблов
        $i = 0;
        foreach ($selectedVariantIds as $variantId) {
            $labelId = $labelIds[$i % count($labelIds)];
            $insertData[] = [
                'product_variant_id' => $variantId,
                'label_id' => $labelId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $i++;
        }

        // Вставляем все связи
        DB::table('product_variant_labels')->insert($insertData);
    }
}
