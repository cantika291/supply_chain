<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_url')->unique(); // cegah duplikat berita yang sama
            $table->string('category')->nullable();  // 'logistics' | 'trade' | 'shipping' | 'economy' | 'geopolitics'
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_cache');
    }
};