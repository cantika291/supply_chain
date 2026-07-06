<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_economic_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('gdp', 20, 2)->nullable();          // dalam USD
            $table->decimal('inflation_rate', 8, 4)->nullable(); // persen
            $table->unsignedBigInteger('population')->nullable();
            $table->decimal('exports_value', 20, 2)->nullable();
            $table->decimal('imports_value', 20, 2)->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'year']); // 1 negara hanya 1 data per tahun
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_economic_data');
    }
};