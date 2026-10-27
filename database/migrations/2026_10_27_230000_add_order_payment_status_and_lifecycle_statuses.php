<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('unrecorded')->after('status');
        });

        DB::table('orders')
            ->where('status', 'paid')
            ->update([
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);

        DB::table('orders')
            ->where('status', 'shipped')
            ->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->update(['status' => 'paid']);

        DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'shipped']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
