<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
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
        'is_public_profile',
        'dues_amount'
    ];

    protected $casts = [
        'date_of_enrollment' => 'date',
        'is_public_profile' => 'boolean',
        'dues_amount' => 'decimal:2'
    ];

    /**
     * Get the full name of the supplier
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
     * Scope for active suppliers (public suppliers)
     */
    public function scopeActive($query)
    {
        return $query->where('is_public_profile', true);
    }

    /**
     * Scope for public profile suppliers
     */
    public function scopePublic($query)
    {
        return $query->where('is_public_profile', true);
    }

    /**
     * Check if supplier has dues
     */
    public function hasDues()
    {
        return $this->dues_amount > 0;
    }

    /**
     * Get formatted dues amount
     */
    public function getFormattedDuesAttribute()
    {
        return number_format($this->dues_amount, 2);
    }

    /**
     * Update dues amount
     */
    public function updateDues($amount)
    {
        $this->dues_amount = max(0, $this->dues_amount + $amount);
        $this->save();
    }

    /**
     * Add to dues
     */
    public function addDues($amount)
    {
        $this->dues_amount += $amount;
        $this->save();
    }

    /**
     * Subtract from dues (payment)
     */
    public function subtractDues($amount)
    {
        $this->dues_amount = max(0, $this->dues_amount - $amount);
        $this->save();
    }
}
