<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flavor extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'flavor_name',
        'flavor_description',
        'flavor_status'
    ];

    protected $casts = [
        'flavor_status' => 'string'
    ];

    /**
     * Get the brand that owns this flavor
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Scope for active flavors
     */
    public function scopeActive($query)
    {
        return $query->where('flavor_status', 'active');
    }

    /**
     * Get the display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->flavor_name;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->flavor_status);
    }

    /**
     * Get full flavor name with brand
     */
    public function getFullNameAttribute()
    {
        return $this->brand->brand_name . ' - ' . $this->flavor_name;
    }
}
