<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sale_target',
        'target_period',
        'commission_percentage'
    ];

    protected $casts = [
        'sale_target' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user that owns the commission agent
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business location through user
     */
    public function businessLocation()
    {
        return $this->hasOneThrough(
            BusinessLocation::class,
            User::class,
            'id', // Foreign key on users table
            'id', // Foreign key on business_locations table
            'user_id', // Local key on commission_agents table
            'business_location_id' // Local key on users table
        );
    }

    /**
     * Get the business currency from user's business location
     */
    public function getBusinessCurrencyAttribute()
    {
        return $this->user?->businessLocation?->business_currency;
    }

    /**
     * Get the phone number from user
     */
    public function getPhoneAttribute()
    {
        return $this->user?->phone;
    }

    /**
     * Get the full name from user
     */
    public function getFullNameAttribute()
    {
        return $this->user?->full_name;
    }

    /**
     * Scope for active commission agents
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate commission amount for a given sale amount
     */
    public function calculateCommission($saleAmount)
    {
        return ($saleAmount * $this->commission_percentage) / 100;
    }

    /**
     * Get target period in human readable format
     */
    public function getTargetPeriodTextAttribute()
    {
        return ucfirst($this->target_period);
    }
}
