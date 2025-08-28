<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'supplier_id',
        'business_location_id',
        'created_by',
        'reference_no',
        'purchase_date',
        'purchase_note',
        'document',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'status',
        'is_active'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

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
     * Get the user who created the purchase
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the inventory items for this purchase
     */
    public function inventoryItems()
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Get the audit logs for this purchase
     */
    public function auditLogs()
    {
        return $this->hasMany(PurchaseAuditLog::class);
    }

    /**
     * Calculate total amount from inventory items
     */
    public function calculateTotalAmount()
    {
        return $this->inventoryItems()->sum('total_order_amount');
    }

    /**
     * Calculate due amount
     */
    public function calculateDueAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if purchase is fully paid
     */
    public function isFullyPaid()
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Check if purchase is partially paid
     */
    public function isPartiallyPaid()
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->total_amount;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->total_amount, 2);
    }

    /**
     * Get formatted paid amount
     */
    public function getFormattedPaidAmountAttribute()
    {
        return number_format($this->paid_amount, 2);
    }

    /**
     * Get formatted due amount
     */
    public function getFormattedDueAmountAttribute()
    {
        return number_format($this->due_amount, 2);
    }

    /**
     * Generate unique reference number
     */
    public static function generateReferenceNo()
    {
        $prefix = 'PUR';
        $date = now()->format('Ymd');
        $lastPurchase = self::where('reference_no', 'like', $prefix . $date . '%')
            ->orderBy('reference_no', 'desc')
            ->first();

        if ($lastPurchase) {
            $lastNumber = intval(substr($lastPurchase->reference_no, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for active purchases
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for purchases by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for purchases by payment status
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope for purchases by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope for purchases by business location
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('business_location_id', $locationId);
    }

    /**
     * Scope for purchases by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchase_date', [$startDate, $endDate]);
    }
} 