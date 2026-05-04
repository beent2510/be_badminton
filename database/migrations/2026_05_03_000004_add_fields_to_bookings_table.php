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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('review_id');
            $table->enum('booking_type', ['fixed', 'adhoc'])->default('adhoc')->after('customer_name');
            $table->enum('booking_purpose', ['regular', 'tournament'])->default('regular')->after('booking_type');
            $table->enum('booking_mode', ['single', 'recurring'])->default('single')->after('booking_purpose');
            $table->foreignId('series_id')->nullable()->after('booking_mode')->constrained('booking_series')->nullOnDelete();
            $table->decimal('deposit_percent', 5, 2)->default(0)->after('final_price');
            $table->decimal('deposit_amount', 15, 2)->default(0)->after('deposit_percent');
            $table->enum('deposit_status', ['none', 'pending', 'paid'])->default('none')->after('deposit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropColumn([
                'customer_name',
                'booking_type',
                'booking_purpose',
                'booking_mode',
                'series_id',
                'deposit_percent',
                'deposit_amount',
                'deposit_status',
            ]);
        });
    }
};
