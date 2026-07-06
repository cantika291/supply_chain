<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sentiments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_cache_id')->unique()->constrained('news_cache')->cascadeOnDelete();
            $table->unsignedInteger('positive_score')->default(0);
            $table->unsignedInteger('negative_score')->default(0);
            $table->string('sentiment'); // 'Positive' | 'Neutral' | 'Negative'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sentiments');
    }
};