<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_sku',
        'product_description',
        'product_category_id',
        'brand_id',
        'flavor_id',
        'unit_id',
        'created_by',
        'default_alert_quantity',
        'default_purchase_price',
        'default_selling_price',
        'default_inclusive_tax_rate',
        'default_exclusive_tax_rate',
        'default_profit_margin',
        'product_expiry',
        'stop_selling_days',
        'is_active',
        'is_public',
        'product_type',
        'product_image'
    ];

    protected $casts = [
        'default_purchase_price' => 'decimal:2',
        'default_selling_price' => 'decimal:2',
        'default_inclusive_tax_rate' => 'decimal:2',
        'default_exclusive_tax_rate' => 'decimal:2',
        'default_profit_margin' => 'decimal:2',
        'product_expiry' => 'date',
        'is_active' => 'boolean',
        'is_public' => 'boolean'
    ];

    /**
     * Get the product category
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Get the brand
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the flavor
     */
    public function flavor()
    {
        return $this->belongsTo(Flavor::class);
    }

    /**
     * Get the unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created the product
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the inventory records for this product
     */
    public function inventory()
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Get current stock quantity
     */
    public function getCurrentStockAttribute()
    {
        return $this->inventory()
            ->where('quantity', '>', 0)
            ->sum('quantity');
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock()
    {
        return $this->current_stock <= $this->default_alert_quantity;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    /**
     * Get the current selling price (from latest inventory or default)
     */
    public function getCurrentSellingPriceAttribute()
    {
        $latestInventory = $this->inventory()
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestInventory ? $latestInventory->selling_price : $this->default_selling_price;
    }

    /**
     * Get the current purchase price (from latest inventory or default)
     */
    public function getCurrentPurchasePriceAttribute()
    {
        $latestInventory = $this->inventory()
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestInventory ? $latestInventory->purchase_price : $this->default_purchase_price;
    }

    /**
     * Calculate selling price with inclusive tax
     */
    public function getSellingPriceWithInclusiveTaxAttribute()
    {
        $price = $this->current_selling_price;
        $taxAmount = ($price * $this->default_inclusive_tax_rate) / 100;
        return $price + $taxAmount;
    }

    /**
     * Calculate selling price with exclusive tax
     */
    public function getSellingPriceWithExclusiveTaxAttribute()
    {
        $price = $this->current_selling_price;
        $taxAmount = ($price * $this->default_exclusive_tax_rate) / 100;
        return $price + $taxAmount;
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public products
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }



    /**
     * Scope for products by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('product_category_id', $categoryId);
    }

    /**
     * Scope for products by brand
     */
    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Generate unique SKU
     */
    public static function generateSku($productName, $brandName = null)
    {
        $baseSku = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $productName));
        $baseSku = substr($baseSku, 0, 6);
        
        if ($brandName) {
            $brandCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $brandName));
            $brandCode = substr($brandCode, 0, 3);
            $baseSku = $brandCode . '-' . $baseSku;
        }
        
        $counter = 1;
        $originalSku = $baseSku;
        
        while (self::where('product_sku', $baseSku)->exists()) {
            $baseSku = $originalSku . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;
        }
        
        return $baseSku;
    }

    /**
     * Get full product name with brand and flavor
     */
    public function getFullNameAttribute()
    {
        $name = $this->product_name;
        
        if ($this->brand) {
            $name = $this->brand->brand_name . ' ' . $name;
        }
        
        if ($this->flavor) {
            $name .= ' - ' . $this->flavor->flavor_name;
        }
        
        return $name;
    }

    /**
     * Get formatted current stock
     */
    public function getFormattedCurrentStockAttribute()
    {
        return number_format($this->current_stock) . ' ' . $this->unit->unit_name;
    }

    /**
     * Get formatted selling price
     */
    public function getFormattedSellingPriceAttribute()
    {
        return number_format($this->current_selling_price, 2);
    }

    /**
     * Get formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute()
    {
        return number_format($this->current_purchase_price, 2);
    }
} 