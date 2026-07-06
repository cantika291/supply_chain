<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->decimal('temperature', 6, 2)->nullable();   // Celsius
            $table->decimal('rainfall', 8, 2)->nullable();      // mm
            $table->decimal('wind_speed', 6, 2)->nullable();    // km/j
            $table->string('storm_risk')->nullable();           // 'low' | 'medium' | 'high'
            $table->timestamp('fetched_at')->nullable();        // waktu data diambil dari API
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_cache');
    }
};