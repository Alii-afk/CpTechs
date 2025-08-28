<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'password',
        'user_role_id',
        'business_location_id',
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
        'total_salary',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_joining' => 'date',
            'contract_time' => 'date',
            'is_public_profile' => 'boolean',
            'is_commission_agent' => 'boolean',
            'basic_salary' => 'decimal:2',
            'medical_allowance' => 'decimal:2',
            'house_allowance' => 'decimal:2',
            'food_allowance' => 'decimal:2',
            'travel_allowance' => 'decimal:2',
            'security' => 'decimal:2',
            'bonus' => 'decimal:2',
            'total_salary' => 'decimal:2',
        ];
    }

    /**
     * Get the user role
     */
    public function userRole()
    {
        return $this->belongsTo(UserRole::class);
    }

    /**
     * Get the business location
     */
    public function businessLocation()
    {
        return $this->belongsTo(BusinessLocation::class);
    }

    /**
     * Get the commission agent record
     */
    public function commissionAgent()
    {
        return $this->hasOne(CommissionAgent::class);
    }

    /**
     * Check if user is a commission agent
     */
    public function isCommissionAgent()
    {
        return $this->is_commission_agent === true;
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->name;
    }

    /**
     * Calculate total salary
     */
    public function calculateTotalSalary()
    {
        $total = 0;
        $total += $this->basic_salary ?? 0;
        $total += $this->medical_allowance ?? 0;
        $total += $this->house_allowance ?? 0;
        $total += $this->food_allowance ?? 0;
        $total += $this->travel_allowance ?? 0;
        $total += $this->security ?? 0;
        $total += $this->bonus ?? 0;
        
        return $total;
    }

    /**
     * Get business currency from business location
     */
    public function getBusinessCurrencyAttribute()
    {
        return $this->businessLocation?->business_currency;
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($tabSlug, $permissionType)
    {
        if (!$this->userRole) {
            return false;
        }
        
        return $this->userRole->hasPermission($tabSlug, $permissionType);
    }

    /**
     * Get all permissions for this user
     */
    public function getAllPermissions()
    {
        if (!$this->userRole) {
            return collect();
        }
        
        return $this->userRole->getAllPermissions();
    }
}
