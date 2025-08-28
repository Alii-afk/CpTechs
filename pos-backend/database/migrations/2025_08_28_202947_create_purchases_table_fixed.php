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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('business_location_id')->constrained('business_locations')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Purchase Details
            $table->date('purchase_date');
            $table->text('purchase_note')->nullable();
            $table->string('document')->nullable(); // File path for uploaded document
            
            // Financial Information
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            
            // Status Information
            $table->enum('status', ['ordered', 'received'])->default('ordered');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'all_dues_cleared'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'check', 'bank_transfer'])->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['supplier_id', 'business_location_id']);
            $table->index(['status', 'payment_status']);
            $table->index(['purchase_date']);
            $table->index(['reference_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
