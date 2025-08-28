<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_name',
        'unit_code',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Scope for active units
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for ordering by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('unit_name', 'asc');
    }

    /**
     * Get the display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->unit_name;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Generate unit code if not provided
     */
    public static function generateUnitCode($unitName)
    {
        $baseCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $unitName));
        $code = substr($baseCode, 0, 6);
        
        $counter = 1;
        $originalCode = $code;
        
        while (self::where('unit_code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return $code;
    }
}
