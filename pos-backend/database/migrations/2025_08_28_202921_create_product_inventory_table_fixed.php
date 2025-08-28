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
        Schema::create('product_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('business_location_id')->constrained('business_locations')->onDelete('cascade');
            
            // Inventory Details
            $table->integer('quantity');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('inclusive_tax_rate', 5, 2)->default(0.00);
            $table->decimal('exclusive_tax_rate', 5, 2)->default(0.00);
            $table->decimal('profit_margin', 5, 2)->default(0.00);
            $table->decimal('one_item_amount', 10, 2);
            $table->decimal('total_order_amount', 10, 2);
            
            // Additional Details
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('invoice_number')->nullable();
            
            // Status
            $table->enum('status', ['available', 'sold', 'damaged', 'expired'])->default('available');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'business_location_id']);
            $table->index(['purchase_id', 'supplier_id']);
            $table->index(['status', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_inventory');
    }
};
