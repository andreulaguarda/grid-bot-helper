<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selected_cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->string('coin_id')->unique();
            $table->string('name');
            $table->string('symbol');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selected_cryptocurrencies');
    }
};