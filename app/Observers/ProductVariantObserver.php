<?php

namespace App\Observers;

use App\Models\ProductFilterIndex;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductVariantObserver
{
    public function saved(ProductVariant $variant): void
    {
        // Индекс не хранит stock и reserved, и это правильно: наличие меняется часто, и его лучше проверять в момент запроса. Но каждое сохранение варианта вызывает полную 
        //пересборку его строк в индексе, в том числе при каждом изменении reserved в заказе. Это работает, но зря нагружает базу. Безопасная оптимизация в начале saved():
        $changes = array_keys($variant->getChanges());
        if ($changes && !array_diff($changes, ['stock', 'reserved', 'updated_at'])) {
            return; // менялось только наличие — индексу это неважно
        }
        // Ранее в методе была только строка ниже
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
