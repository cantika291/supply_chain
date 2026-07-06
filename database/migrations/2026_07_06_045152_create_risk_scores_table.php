<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->unique()->constrained('countries')->cascadeOnDelete();
            $table->decimal('weather_score', 5, 2)->default(0);
            $table->decimal('inflation_score', 5, 2)->default(0);
            $table->decimal('currency_score', 5, 2)->default(0);
            $table->decimal('news_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('risk_level'); // 'Low Risk' | 'Medium Risk' | 'High Risk'
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};