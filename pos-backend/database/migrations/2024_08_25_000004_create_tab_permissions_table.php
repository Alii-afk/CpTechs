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
        Schema::create('tab_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id')->constrained('permission_tabs')->onDelete('cascade');
            $table->foreignId('permission_type_id')->constrained('permission_types')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_permissions');
    }
}; 