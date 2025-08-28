<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    protected $table = 'product_inventory';

    protected $fillable = [
        'product_id',
        'purchase_id',
        'supplier_id',
        'business_location_id',
        'quantity',
        'purchase_price',
        'selling_price',
        'inclusive_tax_rate',
        'exclusive_tax_rate',
        'profit_margin',
        'tax_amount',
        'profit_amount',
        'one_item_amount',
        'total_order_amount',
        'batch_number',
        'expiry_date',
        'invoice_number',
        'inventory_type',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'inclusive_tax_rate' => 'decimal:2',
        'exclusive_tax_rate' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'one_item_amount' => 'decimal:2',
        'total_order_amount' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the business location
     */
    public function businessLocation()
    {
        return $this->belongsTo(BusinessLocation::class);
    }

    /**
     * Get the purchase
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Calculate total purchase value
     */
    public function getTotalPurchaseValueAttribute()
    {
        return $this->quantity * $this->purchase_price;
    }

    /**
     * Calculate total selling value
     */
    public function getTotalSellingValueAttribute()
    {
        return $this->quantity * $this->selling_price;
    }

    /**
     * Calculate total profit
     */
    public function getTotalProfitAttribute()
    {
        return $this->total_selling_value - $this->total_purchase_value;
    }

    /**
     * Calculate one item amount (purchase price + tax + profit)
     */
    public function calculateOneItemAmount()
    {
        return $this->purchase_price + $this->tax_amount + $this->profit_amount;
    }

    /**
     * Calculate total order amount
     */
    public function calculateTotalOrderAmount()
    {
        return $this->quantity * $this->purchase_price;
    }

    /**
     * Calculate tax amount based on purchase price
     */
    public function calculateTaxAmount()
    {
        // You can implement different tax calculation logic here
        // For now, using a simple percentage calculation
        return ($this->purchase_price * $this->inclusive_tax_rate) / 100;
    }

    /**
     * Calculate profit amount based on purchase price
     */
    public function calculateProfitAmount()
    {
        return ($this->purchase_price * $this->profit_margin) / 100;
    }

    /**
     * Calculate profit percentage
     */
    public function getProfitPercentageAttribute()
    {
        if ($this->total_purchase_value > 0) {
            return (($this->total_selling_value - $this->total_purchase_value) / $this->total_purchase_value) * 100;
        }
        return 0;
    }

    /**
     * Calculate selling price with inclusive tax
     */
    public function getSellingPriceWithInclusiveTaxAttribute()
    {
        $taxAmount = ($this->selling_price * $this->inclusive_tax_rate) / 100;
        return $this->selling_price + $taxAmount;
    }

    /**
     * Calculate selling price with exclusive tax
     */
    public function getSellingPriceWithExclusiveTaxAttribute()
    {
        $taxAmount = ($this->selling_price * $this->exclusive_tax_rate) / 100;
        return $this->selling_price + $taxAmount;
    }

    /**
     * Check if inventory is expired
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if inventory is expiring soon
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Scope for active inventory
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inventory with stock
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope for inventory by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('inventory_type', $type);
    }

    /**
     * Scope for inventory by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope for inventory by business location
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('business_location_id', $locationId);
    }

    /**
     * Scope for expiring inventory
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    /**
     * Scope for expired inventory
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Get formatted total purchase value
     */
    public function getFormattedTotalPurchaseValueAttribute()
    {
        return number_format($this->total_purchase_value, 2);
    }

    /**
     * Get formatted total selling value
     */
    public function getFormattedTotalSellingValueAttribute()
    {
        return number_format($this->total_selling_value, 2);
    }

    /**
     * Get formatted total profit
     */
    public function getFormattedTotalProfitAttribute()
    {
        return number_format($this->total_profit, 2);
    }

    /**
     * Get formatted profit percentage
     */
    public function getFormattedProfitPercentageAttribute()
    {
        return number_format($this->profit_percentage, 2) . '%';
    }

    /**
     * Get formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute()
    {
        return number_format($this->purchase_price, 2);
    }

    /**
     * Get formatted selling price
     */
    public function getFormattedSellingPriceAttribute()
    {
        return number_format($this->selling_price, 2);
    }
} 