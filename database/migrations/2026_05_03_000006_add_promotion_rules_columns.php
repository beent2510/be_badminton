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
        Schema::table('promotions', function (Blueprint $table) {
            $table->enum('promo_category', ['general', 'org_event', 'peak_hour', 'multi_court'])->default('general')->after('discount_type');
            $table->decimal('min_hours', 5, 2)->default(0)->after('promo_category');
            $table->unsignedSmallInteger('min_courts')->default(1)->after('min_hours');
            $table->boolean('requires_peak_overlap')->default(false)->after('min_courts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['promo_category', 'min_hours', 'min_courts', 'requires_peak_overlap']);
        });
    }
};
