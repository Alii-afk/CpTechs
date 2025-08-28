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
        Schema::table('users', function (Blueprint $table) {
            // Profile fields
            $table->string('username')->nullable()->after('name');
            $table->string('first_name')->nullable()->after('username');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->enum('gender', ['Male', 'Female'])->nullable()->after('phone');
            $table->date('date_of_joining')->nullable()->after('gender');
            $table->date('contract_time')->nullable()->after('date_of_joining');
            $table->string('profile_image')->nullable()->after('contract_time');
            $table->text('address')->nullable()->after('profile_image');
            $table->boolean('is_public_profile')->default(false)->after('address');
            $table->boolean('is_commission_agent')->default(false)->after('is_public_profile');
            
            // Salary fields
            $table->decimal('basic_salary', 10, 2)->nullable()->after('is_commission_agent');
            $table->decimal('medical_allowance', 10, 2)->nullable()->after('basic_salary');
            $table->decimal('house_allowance', 10, 2)->nullable()->after('medical_allowance');
            $table->decimal('food_allowance', 10, 2)->nullable()->after('house_allowance');
            $table->decimal('travel_allowance', 10, 2)->nullable()->after('food_allowance');
            $table->decimal('security', 10, 2)->nullable()->after('travel_allowance');
            $table->decimal('bonus', 10, 2)->nullable()->after('security');
            $table->decimal('total_salary', 10, 2)->nullable()->after('bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'first_name',
                'last_name',
                'phone',
                'gender',
                'date_of_joining',
                'contract_time',
                'profile_image',
                'address',
                'is_public_profile',
                'is_commission_agent',
                'basic_salary',
                'medical_allowance',
                'house_allowance',
                'food_allowance',
                'travel_allowance',
                'security',
                'bonus',
                'total_salary'
            ]);
        });
    }
};
