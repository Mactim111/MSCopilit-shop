<?php

namespace App\Observers;

use App\Models\ProductFilterIndex;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductVariantObserver
{
    public function saved(ProductVariant $variant): void
    {
        $this->rebuildIndex($variant);
    }

    public function deleted(ProductVariant $variant): void
    {
        ProductFilterIndex::where('product_variant_id', $variant->id)->delete();
    }

    private function rebuildIndex(ProductVariant $variant): void
    {
        // Загружаем свежие данные — вариант мог быть сохранён до загрузки relations.
        $variant->loadMissing(['product', 'propertyOptions.property']);

        $product = $variant->product;

        if (!$product) {
            return;
        }

        // Удаляем старые записи индекса для этого варианта.
        ProductFilterIndex::where('product_variant_id', $variant->id)->delete();

        if ($variant->propertyOptions->isEmpty()) {
            return;
        }

        // Собираем новые записи пачкой — один INSERT вместо N запросов.
        $rows = $variant->propertyOptions->map(fn ($option) => [
            'category_id'        => $product->category_id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'property_id'        => $option->property_id,
            'value_slug'         => $option->slug,
            // Копируем numeric_value для range-фильтров без JOIN.
            'numeric_value'      => $option->numeric_value,
            'price'              => $variant->price,
        ])->all();

        DB::table('product_filter_index')->insert($rows);
    }
}
