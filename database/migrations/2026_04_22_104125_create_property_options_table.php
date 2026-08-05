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
        Schema::create('property_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->string('slug');
            $table->text('excerpt')->nullable();
            // Числовое представление для range-фильтров и сортировки.
            // Примеры: «6 кг» → 6.00, «1200 об/мин» → 1200.00
            // NULL для нечисловых свойств (checkbox / radio / toggle).
            $table->decimal('numeric_value', 10, 2)->nullable();

            // Задел под цветовые свитчеры: hex-код для type='color'-подобных свойств.
            // Если заказчик захочет цветные кружки вместо текстовых плиток —
            // поле уже есть, достаточно заполнить и поменять шаблон.
            // Пример: «Чёрный» → «#1a1a1a», «Синий» → «#4169e1»
            $table->string('color_hex', 7)->nullable();
            // $table->integer('position')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['property_id', 'slug']);
            $table->index(['property_id', 'numeric_value'], 'idx_po_numeric');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_options');
    }
};
