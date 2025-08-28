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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_sku')->unique();
            $table->text('product_description')->nullable();
            
            // Product Classification
            $table->foreignId('product_category_id')->constrained('product_categories');
            $table->foreignId('brand_id')->constrained('brands');
            $table->foreignId('flavor_id')->nullable()->constrained('flavors');
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Default Settings (can be overridden in inventory)
            $table->integer('default_alert_quantity')->default(10);
            $table->decimal('default_purchase_price', 10, 2)->default(0.00);
            $table->decimal('default_selling_price', 10, 2)->default(0.00);
            $table->decimal('default_inclusive_tax_rate', 5, 2)->default(0.00); // Inclusive tax percentage
            $table->decimal('default_exclusive_tax_rate', 5, 2)->default(0.00); // Exclusive tax percentage
            $table->decimal('default_profit_margin', 5, 2)->default(0.00); // Percentage
            
            // Product Lifecycle
            $table->date('product_expiry')->nullable();
            $table->integer('stop_selling_days')->default(30); // Days before expiry to stop selling
            
            // Product Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // Visible in public catalog
            $table->enum('product_type', ['physical', 'digital', 'service'])->default('physical');
            
            // Product Images
            $table->string('product_image')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['product_sku']);
            $table->index(['product_category_id', 'brand_id']);
            $table->index(['is_active', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
