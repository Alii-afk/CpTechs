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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->enum('contact_type', ['Individual', 'Business'])->default('Individual');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email')->unique();
            $table->string('business_website')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('business_location')->nullable();
            $table->enum('business_status', ['Shop', 'Website', 'Dealer'])->default('Shop');
            $table->date('date_of_enrollment')->nullable();
            $table->string('profile_image')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_public_profile')->default(false);
            $table->decimal('dues_amount', 15, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
