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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->index(); // 'home', 'variants/show' и т.д., Чтобы система понимала, на какой странице что выводить (имя шаблона страницы)
            $table->string('title')->nullable(); // Заголовок блока, например "Хиты продаж"
            $table->string('type');              // 'product_slider', 'double_banner', 'one_banner'
            $table->string('source_type');       // 'best_sellers', 'new_arrivals', 'related', 'manual'
            $table->string('source_value')->nullable(); // Сюда можно записать ID категории или группы картинок
            $table->boolean('show_tags')->default(false); // Показывать ли БЛОК ТЕГОВ над слайдером
            $table->integer('order')->default(0); // Порядок вывода на странице
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
