<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'location_id',
        'landmark',
        'city',
        'zip_code',
        'state',
        'country',
        'mobile',
        'email',
        'website',
        'business_currency',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
        'business_currency' => 'string'
    ];

    /**
     * Get the users associated with this business location
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Generate a unique location ID
     */
    public static function generateLocationId()
    {
        $lastLocation = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastLocation ? intval(substr($lastLocation->location_id, 4)) + 1 : 1;
        return 'LOC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for active business locations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the full address
     */
    public function getFullAddressAttribute()
    {
        $address = $this->city . ', ' . $this->state . ', ' . $this->country;
        if ($this->landmark) {
            $address = $this->landmark . ', ' . $address;
        }
        return $address;
    }
}
