<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'category_description',
        'category_status',
        'category_code'
    ];

    protected $casts = [
        'category_status' => 'string'
    ];

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('category_status', 'active');
    }

    /**
     * Scope for ordering by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('category_name', 'asc');
    }

    /**
     * Get the display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->category_name;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->category_status);
    }

    /**
     * Generate category code if not provided
     */
    public static function generateCategoryCode($categoryName)
    {
        $baseCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $categoryName));
        $code = substr($baseCode, 0, 8);
        
        $counter = 1;
        $originalCode = $code;
        
        while (self::where('category_code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return $code;
    }
} 