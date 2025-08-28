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
        Schema::create('commission_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('sale_target', 12, 2);
            $table->enum('target_period', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->decimal('commission_percentage', 5, 2); // Up to 999.99%
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure one commission agent per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_agents');
    }
};
