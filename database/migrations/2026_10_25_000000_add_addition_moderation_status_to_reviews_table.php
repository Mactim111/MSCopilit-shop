<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('addition_moderation_status', 20)
                ->nullable()
                ->after('addition_updated_at');
        });

        DB::table('reviews')
            ->whereNotNull('addition')
            ->update(['addition_moderation_status' => 'pending']);

        DB::table('reviews')
            ->whereNotNull('addition')
            ->where('addition_is_published', true)
            ->update(['addition_moderation_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('addition_moderation_status');
        });
    }
};
