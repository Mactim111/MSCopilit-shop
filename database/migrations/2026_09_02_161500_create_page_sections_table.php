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
            $table->string('title')->nullable(); // "Хиты продаж"
            $table->string('type');              // "product_slider", "double_banner"
            $table->string('source_type');       // "best_sellers", "new_arrivals", "manual_category"
            $table->integer('source_id')->nullable(); // ID категории, если выбрали ручной набор
            $table->boolean('show_tags')->default(false);
            $table->integer('order')->default(0);
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
