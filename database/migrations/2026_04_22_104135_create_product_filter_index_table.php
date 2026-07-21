<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Денормализованная таблица для быстрой фильтрации на странице категории.
        // Перестраивается автоматически через ProductVariantObserver
        // при сохранении/удалении варианта товара.
        // Администратор эту таблицу не видит и не редактирует.
        Schema::create('product_filter_index', function (Blueprint $table) {
            $table->id();

            // Денормализуем сюда все нужные id, чтобы фильтрация
            // обходилась без JOIN на products / product_variants.
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('property_id');

            // Slug значения опции — для checkbox/radio/toggle фильтров.
            $table->string('value_slug', 100);

            // Числовое значение — копия из property_options.numeric_value.
            // Используется для range-фильтров без JOIN.
            $table->decimal('numeric_value', 10, 2)->nullable();

            // Цена варианта — для ценового range-фильтра.
            $table->decimal('price', 10, 2)->nullable();

            // Основной индекс: category + property + value — основа фильтрации.
            $table->index(
                ['category_id', 'property_id', 'value_slug'],
                'idx_filter_main'
            );
            // Индекс для range-фильтров по числовым свойствам.
            $table->index(
                ['category_id', 'property_id', 'numeric_value'],
                'idx_filter_numeric'
            );
            // Индекс для ценового range.
            $table->index(['category_id', 'price'], 'idx_filter_price');
            // Индекс для быстрого пересчёта при обновлении варианта.
            $table->index('product_variant_id', 'idx_filter_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_filter_index');
    }
};
