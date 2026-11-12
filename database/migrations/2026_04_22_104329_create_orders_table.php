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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('new'); // new, paid, shipped, cancelled
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('payment_status')->default('unrecorded');
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('delivery_method')->default('courier');
            $table->decimal('delivery_price', 10, 2)->default(0);
            $table->string('payment_method')->default('cash_on_delivery');
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->index(['user_id', 'status', 'cancellation_requested_at'], 'orders_profile_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
