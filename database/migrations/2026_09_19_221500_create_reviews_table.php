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
            $table->text('addition')->nullable();
            $table->timestamp('addition_updated_at')->nullable();
            $table->boolean('addition_is_published')->default(false);
            $table->boolean('is_published')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unsignedTinyInteger('active_review')
                ->nullable()
                ->virtualAs('IF(`deleted_at` IS NULL, 1, NULL)');
            $table->unique(
                ['user_id', 'product_variant_id', 'active_review'],
                'reviews_one_active_per_user_variant_unique'
            );
            $table->index(['product_variant_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
