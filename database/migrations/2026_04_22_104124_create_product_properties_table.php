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
        Schema::create('product_properties', function (Blueprint $table) {
            $table->primary(['product_id', 'property_id']);
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->boolean('used_for_variant_card')->default(0); // ключевое отличительное свойство для варианта, обязательное! для вывода в карточке ВАРИАНТА ТОВАРА на ЕГО странице
            $table->integer('position_in_variant_card')->default(0); // порядок в карточке варианта
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_properties');
    }
};
