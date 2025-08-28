<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'brand_description',
        'brand_status',
        'brand_logo'
    ];

    protected $casts = [
        'brand_status' => 'string'
    ];

    /**
     * Get the flavors for this brand
     */
    public function flavors()
    {
        return $this->hasMany(Flavor::class);
    }

    /**
     * Get active flavors for this brand
     */
    public function activeFlavors()
    {
        return $this->hasMany(Flavor::class)->where('flavor_status', 'active');
    }

    /**
     * Scope for active brands
     */
    public function scopeActive($query)
    {
        return $query->where('brand_status', 'active');
    }

    /**
     * Get the display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->brand_name;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->brand_status);
    }

    /**
     * Check if brand has any flavors
     */
    public function hasFlavors()
    {
        return $this->flavors()->count() > 0;
    }

    /**
     * Get flavors count
     */
    public function getFlavorsCountAttribute()
    {
        return $this->flavors()->count();
    }

    /**
     * Get active flavors count
     */
    public function getActiveFlavorsCountAttribute()
    {
        return $this->activeFlavors()->count();
    }

    /**
     * Sync all flavors status with brand status
     */
    public function syncFlavorStatuses()
    {
        return $this->flavors()->update(['flavor_status' => $this->brand_status]);
    }

    /**
     * Boot method to automatically sync flavor statuses when brand status changes
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($brand) {
            if ($brand->wasChanged('brand_status')) {
                $brand->syncFlavorStatuses();
            }
        });
    }
}
