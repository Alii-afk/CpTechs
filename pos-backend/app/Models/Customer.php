<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_type',
        'first_name',
        'last_name',
        'business_name',
        'email',
        'business_website',
        'whatsapp',
        'business_location',
        'business_status',
        'date_of_enrollment',
        'profile_image',
        'address',
        'shipping_address',
        'is_public_profile',
        'credit_limit',
        'current_balance'
    ];

    protected $casts = [
        'date_of_enrollment' => 'date',
        'is_public_profile' => 'boolean',
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2'
    ];

    /**
     * Get the full name of the customer
     */
    public function getFullNameAttribute()
    {
        if ($this->contact_type === 'Individual') {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        return $this->business_name;
    }

    /**
     * Get the display name (full name or business name)
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name ?: $this->business_name ?: $this->email;
    }

    /**
     * Scope for public profile customers
     */
    public function scopePublic($query)
    {
        return $query->where('is_public_profile', true);
    }

    /**
     * Check if customer has available credit
     */
    public function hasAvailableCredit()
    {
        return $this->current_balance < $this->credit_limit;
    }

    /**
     * Get available credit amount
     */
    public function getAvailableCreditAttribute()
    {
        return max(0, $this->credit_limit - $this->current_balance);
    }

    /**
     * Get formatted credit limit
     */
    public function getFormattedCreditLimitAttribute()
    {
        return number_format($this->credit_limit, 2);
    }

    /**
     * Get formatted current balance
     */
    public function getFormattedCurrentBalanceAttribute()
    {
        return number_format($this->current_balance, 2);
    }

    /**
     * Get formatted available credit
     */
    public function getFormattedAvailableCreditAttribute()
    {
        return number_format($this->available_credit, 2);
    }

    /**
     * Update current balance
     */
    public function updateBalance($amount)
    {
        $this->current_balance = max(0, $this->current_balance + $amount);
        $this->save();
    }

    /**
     * Add to current balance (for purchases on credit)
     */
    public function addToBalance($amount)
    {
        $this->current_balance += $amount;
        $this->save();
    }

    /**
     * Subtract from current balance (for payments)
     */
    public function subtractFromBalance($amount)
    {
        $this->current_balance = max(0, $this->current_balance - $amount);
        $this->save();
    }

    /**
     * Check if customer can make a purchase of given amount
     */
    public function canPurchase($amount)
    {
        return ($this->current_balance + $amount) <= $this->credit_limit;
    }
}
