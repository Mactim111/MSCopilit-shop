<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Category;

class ProductVariantImagesSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем все варианты товаров
        $variants = DB::table('product_variants')->get();

        foreach ($variants as $variant) {

            // 1. Получаем товар
            $product = DB::table('products')->where('id', $variant->product_id)->first();
            if (!$product) continue;

            // 2. Получаем подкатегорию товара напрямую
            $category = Category::find($product->category_id);
            if (!$category) continue;


            if (!$category) continue;

            // 3. Получаем родителя категории (категория)
            $parentCategory = DB::table('categories')->where('id', $category->parent_id)->first();
            if (!$parentCategory) continue;

            // 4. Получаем родителя родителя (группа категорий)
            $groupCategory = DB::table('categories')->where('id', $parentCategory->parent_id)->first();
            if (!$groupCategory) continue;

            // 5. Формируем путь к папке галереи
            $basePath = storage_path(
                'app/public/images/products/galleries/' .
                $groupCategory->slug . '/' .
                $parentCategory->slug . '/' .
                $category->slug . '/' .
                $product->slug . '/' .
                $variant->slug . '/'
            );
// dump($basePath);
            // 6. Проверяем, существует ли папка
            if (!File::exists($basePath)) {
                continue;
            }

            // 7. Получаем список файлов
            $files = File::files($basePath);

            $insertData = [];

            foreach ($files as $file) {
                $filename = $file->getFilename();

                // Позиция = цифра из имени файла
                $position = (int) pathinfo($filename, PATHINFO_FILENAME);

                $insertData[] = [
                    'product_variant_id' => $variant->id,
                    'path' => 'storage/images/products/galleries/' .
                        $groupCategory->slug . '/' .
                        $parentCategory->slug . '/' .
                        $category->slug . '/' .
                        $product->slug . '/' .
                        $variant->slug . '/' . $filename,
                    'position' => $position,
                    'alt' => $variant->title,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertData)) {
                DB::table('product_variant_images')->insert($insertData);
            }
        }
    }
}
