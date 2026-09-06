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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete(); // Если удалили заказ целиком — удаляем и позиции
            $table->foreignId('product_variant_id')
                ->nullable() // Это позволит базе данных записать NULL при удалении
                ->constrained('product_variants')
                //НИЖЕ! база данных позволит удалить товар, просто занулит ID в заказе (т.к. у нас есть SoftDeletes это просто подстраховка на случай 
                // «жесткого» удаления. При обычном удалении (Soft Delete) база данных вообще не будет дергать эти ключи.).
                ->nullOnDelete();
            $table->string('title');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
