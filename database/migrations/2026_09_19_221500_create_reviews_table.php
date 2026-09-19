<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('advantages')->nullable();
            $table->text('disadvantages')->nullable();
            $table->text('comment');
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'product_variant_id']);
            $table->index(['product_variant_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
