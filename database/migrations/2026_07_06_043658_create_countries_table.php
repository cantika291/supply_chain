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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // Nama negara, contoh: "Indonesia"
            $table->string('official_name')->nullable(); // Nama resmi, contoh: "Republic of Indonesia"
            $table->string('cca3', 3)->unique();          // Kode ISO 3 huruf, contoh: "IDN" — dipakai untuk sinkron API
            $table->string('cca2', 2)->nullable();        // Kode ISO 2 huruf, contoh: "ID"
            $table->string('region')->nullable();         // Contoh: "Asia"
            $table->string('subregion')->nullable();      // Contoh: "South-Eastern Asia"
            $table->string('capital')->nullable();        // Ibu kota
            $table->string('currency_code', 3)->nullable(); // Contoh: "IDR"
            $table->string('currency_name')->nullable();    // Contoh: "Indonesian Rupiah"
            $table->string('language')->nullable();         // Bahasa utama
            $table->decimal('latitude', 10, 6)->nullable();  // Untuk marker Leaflet.js
            $table->decimal('longitude', 10, 6)->nullable(); // Untuk marker Leaflet.js
            $table->string('flag_url')->nullable();          // URL gambar bendera
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};