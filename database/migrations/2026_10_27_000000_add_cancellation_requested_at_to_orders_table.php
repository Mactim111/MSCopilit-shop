<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('cancellation_requested_at')
                ->nullable()
                ->after('status');
            $table->index(['user_id', 'status', 'cancellation_requested_at'], 'orders_profile_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_profile_status_index');
            $table->dropColumn('cancellation_requested_at');
        });
    }
};
