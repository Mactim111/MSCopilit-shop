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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('article')->unique(); // артикул
            $table->string('slug')->unique();        
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();           
            $table->decimal('price', 8, 2);
            $table->decimal('old_price', 8, 2)->default(0);
            $table->integer('stock')->default(0);
            // ниже поле для обозначения позиции ВАРИАНТА ТОВАРА при выводе ВСЕХ! ВАРИАНТОВ ТОВАРОВ (ПОРЯДКА ИХ ВЫВОДА НА СТРАНИЦЕ)
            // по аналогии с полем 'position' в таблице ГАЛЕРЕИ ИЗОБРАЖЕНИЙ для определения Порядка Вывода картинок в Галерее на странице ВАРИАНТА ТОВАРА
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false); // является ли вариант товара "основным" (по умолчанию) для данного товара
            $table->boolean('is_active')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
