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
        Schema::create('court_peak_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->onDelete('cascade');
            $table->integer('day_of_week')->unsigned(); // 0 (Sunday) to 6 (Saturday)
            $table->time('from_time');
            $table->time('to_time');
            $table->decimal('price_peak_hour', 15, 2)->default(0); // New field for peak hour price
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_peak_hours');
    }
};
