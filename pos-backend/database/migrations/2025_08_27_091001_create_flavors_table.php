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
        Schema::create('flavors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->string('flavor_name');
            $table->text('flavor_description')->nullable();
            $table->enum('flavor_status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            // Ensure unique flavor names within the same brand
            $table->unique(['brand_id', 'flavor_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flavors');
    }
};
