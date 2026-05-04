<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE booking_series MODIFY COLUMN interval_unit ENUM('week','month','quarter','year') DEFAULT 'month'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE booking_series MODIFY COLUMN interval_unit ENUM('month','quarter','year') DEFAULT 'month'");
    }
};
