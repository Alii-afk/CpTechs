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
        Schema::create('business_locations', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('location_id')->unique();
            $table->string('landmark')->nullable();
            $table->string('city');
            $table->string('zip_code');
            $table->string('state');
            $table->string('country');
            $table->string('mobile');
            $table->string('email')->unique();
            $table->string('website')->nullable();
            $table->enum('business_currency', ['usd', 'eur', 'gbp', 'pkr', 'aed'])->default('usd');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
