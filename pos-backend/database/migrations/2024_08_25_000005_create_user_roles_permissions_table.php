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
        Schema::create('user_roles_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_role_id')->constrained('user_roles')->onDelete('cascade');
            $table->foreignId('tab_permission_id')->constrained('tab_permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles_permissions');
    }
}; 