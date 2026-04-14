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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->integer('usage_count')->default(0);
            $table->string('code')->unique();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('discount_value', 15, 2);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->integer('max_usage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
