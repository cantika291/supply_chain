<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);       // contoh: "IDR"
            $table->decimal('rate_to_usd', 20, 6);     // 1 USD = berapa currency_code ini
            $table->date('rate_date');                 // tanggal kurs berlaku
            $table->timestamps();

            $table->unique(['currency_code', 'rate_date']); // 1 mata uang, 1 kurs per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};