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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name')->unique();
            $table->text('category_description')->nullable();
            $table->enum('category_status', ['active', 'inactive'])->default('active');
            $table->string('category_image')->nullable();
            $table->string('category_code')->unique()->nullable(); // For internal reference
            $table->integer('sort_order')->default(0); // For ordering categories
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
}; 