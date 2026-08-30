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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');          // «Цвет корпуса», «Объём RAM»
            $table->string('slug')->unique();  // «color», «ram»
            $table->text('excerpt')->nullable();
            // $table->enum('type', ['string', 'integer', 'float', 'boolean', 'color', 'range'])->default('string');
            // Тип виджета фильтра в сайдбаре каталога:
            //   checkbox — мультивыбор          (Бренд: LG ✓, Samsung ✓)
            //   radio    — одиночный выбор       (Тип загрузки: Фронтальная •)
            //   range    — числовой диапазон     (Вес загрузки: 5 — 9 кг)
            //   toggle   — булево есть/нет       (Есть NFC: ●)
            $table->enum('type', ['checkbox', 'radio', 'range', 'toggle'])
                  ->default('checkbox');

            // --- Зона ответственности: ФИЛЬТРЫ на странице категории ---
            // Показывать ли свойство в сайдбаре фильтров.

            // Порядок вывода в сайдбаре фильтров.
            $table->boolean('used_for_filters')->default(0); // выводить ли в блоке ФИЛЬТРОВ на странице ПОДКАТЕГОРИИ
            // Порядок вывода в сайдбаре фильтров.
            $table->integer('position_in_filters')->default(0); // порядок в фильтрах

            // Шаг слайдера (0.01, 0.1, 1, 100, ...)
            $table->decimal('step', 10, 4)->default(1);

            // Количество знаков после запятой (0, 1, 2)
            $table->unsignedTinyInteger('digits')->default(0);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
