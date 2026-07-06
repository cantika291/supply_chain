<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_score_id')->constrained('risk_scores')->cascadeOnDelete();
            $table->decimal('total_score', 5, 2);
            $table->string('risk_level');
            $table->date('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_score_histories');
    }
};