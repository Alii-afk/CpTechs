<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\ProductInventory;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        try {
            $query = Purchase::with(['supplier', 'businessLocation', 'createdBy', 'inventoryItems.product']);

            // Apply filters
            if ($request->has('supplier_id')) {
                $query->bySupplier($request->supplier_id);
            }

            if ($request->has('business_location_id')) {
                $query->byLocation($request->business_location_id);
            }

            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('payment_status')) {
                $query->byPaymentStatus($request->payment_status);
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->byDateRange($request->date_from, $request->date_to);
            }

            // Search by reference number
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('reference_no', 'like', "%{$search}%");
            }

            $purchases = $query->orderBy('created_at', 'desc')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $purchases
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchases: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        try {

            
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'business_location_id' => 'required|exists:business_locations,id',
                'purchase_date' => 'required|date',
                'reference_no' => 'nullable|string|unique:purchases',
                'purchase_note' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf,csv,zip,doc,docx,jpeg,jpg,png|max:5120',
                'paid_amount' => 'required|numeric|min:0',
                'payment_status' => 'required|in:pending,paid,partial,all_dues_cleared',
                'payment_method' => 'nullable|in:cash,card,check,bank_transfer',
                'inventory_items' => 'required|array|min:1',
                'inventory_items.*.product_id' => 'required|exists:products,id',
                'inventory_items.*.quantity' => 'required|integer|min:1',
                'inventory_items.*.purchase_price' => 'required|numeric|min:0',
                'inventory_items.*.inclusive_tax_rate' => 'required|numeric|min:0|max:100',
                'inventory_items.*.exclusive_tax_rate' => 'required|numeric|min:0|max:100',
                'inventory_items.*.profit_margin' => 'required|numeric|min:0|max:100',
                'inventory_items.*.one_item_amount' => 'required|numeric|min:0',
                'inventory_items.*.total_order_amount' => 'required|numeric|min:0',
                'inventory_items.*.batch_number' => 'nullable|string',
                'inventory_items.*.expiry_date' => 'nullable|date',
                'inventory_items.*.invoice_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate reference number if not provided
            $referenceNo = $request->reference_no;
            if (!$referenceNo) {
                $referenceNo = Purchase::generateReferenceNo();
            }

            // Handle document upload
            $documentPath = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/purchases'), $safeName);
                $documentPath = $safeName;
            }

            // Calculate total amount from inventory items
            $totalAmount = collect($request->inventory_items)->sum('total_order_amount');
            $dueAmount = $totalAmount - $request->paid_amount;

            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'business_location_id' => $request->business_location_id,
                'created_by' => 4, // Using existing user ID - in production this would come from authentication
                'reference_no' => $referenceNo,
                'purchase_date' => $request->purchase_date,
                'purchase_note' => $request->purchase_note,
                'document' => $documentPath,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'status' => $request->status ?? 'ordered'
            ]);

            // Note: Supplier dues are now calculated dynamically in getSupplierDues method
            // No need to update supplier.dues_amount field anymore

            // Create inventory items ONLY if status is 'received'
            if ($request->status === 'received') {
                foreach ($request->inventory_items as $item) {
                    // Calculate tax amounts from rates
                    $purchasePrice = $item['purchase_price'];
                    $inclusiveTaxRate = $item['inclusive_tax_rate'];
                    $exclusiveTaxRate = $item['exclusive_tax_rate'];
                    $profitMargin = $item['profit_margin'];
                    
                    $inclusiveTaxAmount = ($purchasePrice * $inclusiveTaxRate) / 100;
                    $exclusiveTaxAmount = ($purchasePrice * $exclusiveTaxRate) / 100;
                    $totalTaxAmount = $inclusiveTaxAmount + $exclusiveTaxAmount;
                    $profitAmount = (($purchasePrice + $totalTaxAmount) * $profitMargin) / 100;
                    
                    ProductInventory::create([
                        'product_id' => $item['product_id'],
                        'purchase_id' => $purchase->id,
                        'supplier_id' => $request->supplier_id,
                        'business_location_id' => $request->business_location_id,
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'selling_price' => $item['one_item_amount'], // Use one_item_amount as selling price
                        'inclusive_tax_rate' => $inclusiveTaxRate,
                        'exclusive_tax_rate' => $exclusiveTaxRate,
                        'profit_margin' => $profitMargin,
                        'tax_amount' => $totalTaxAmount,
                        'profit_amount' => $profitAmount,
                        'one_item_amount' => $item['one_item_amount'],
                        'total_order_amount' => $item['total_order_amount'],
                        'batch_number' => $item['batch_number'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'invoice_number' => $item['invoice_number'] ?? null,
                        'inventory_type' => 'purchase',
                        'is_active' => true
                    ]);
                }
            }

            // Update supplier dues if payment is pending
            if ($request->payment_status === 'pending' || $request->payment_status === 'partial') {
                $supplier = Supplier::find($request->supplier_id);
                $supplier->updateDues($dueAmount);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase created successfully',
                'data' => $purchase->load(['supplier', 'businessLocation', 'createdBy', 'inventoryItems.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase
     */
    public function show($id)
    {
        try {
            $purchase = Purchase::with(['supplier', 'businessLocation', 'createdBy', 'inventoryItems.product'])
                ->find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $purchase
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'business_location_id' => 'required|exists:business_locations,id',
                'purchase_date' => 'required|date',
                'purchase_note' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf,csv,zip,doc,docx,jpeg,jpg,png|max:5120',
                'paid_amount' => 'required|numeric|min:0',
                'payment_status' => 'required|in:pending,paid,partial,all_dues_cleared',
                'payment_method' => 'nullable|in:cash,card,check,bank_transfer',
                'status' => 'required|in:ordered,received'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle document upload
            $documentPath = $purchase->document;
            if ($request->hasFile('document')) {
                // Delete old document if exists
                if ($purchase->document) {
                    $oldDocumentPath = public_path('uploads/purchases/' . $purchase->document);
                    if (file_exists($oldDocumentPath)) {
                        unlink($oldDocumentPath);
                    }
                }

                $file = $request->file('document');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/purchases'), $safeName);
                $documentPath = $safeName;
            }

            // Calculate due amount
            $dueAmount = $purchase->total_amount - $request->paid_amount;

            // Update purchase
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'business_location_id' => $request->business_location_id,
                'purchase_date' => $request->purchase_date,
                'purchase_note' => $request->purchase_note,
                'document' => $documentPath,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'status' => $request->status
            ]);

            // Note: Supplier dues are now calculated dynamically in getSupplierDues method
            // No need to update supplier.dues_amount field anymore

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase updated successfully',
                'data' => $purchase->load(['supplier', 'businessLocation', 'createdBy', 'inventoryItems.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found'
                ], 404);
            }

            // Check if purchase is older than 30 days
            if ($purchase->created_at < now()->subDays(30)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete purchases older than 30 days. Please contact administrator for assistance.'
                ], 400);
            }

            // Check if purchase status is "received" - cannot delete received purchases
            if ($purchase->status === 'received') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete received purchases. Received purchases affect inventory and cannot be deleted. Please cancel the purchase instead.'
                ], 400);
            }

            // Check if any inventory items have been sold
            $inventoryItems = $purchase->inventoryItems;
            foreach ($inventoryItems as $item) {
                // Check if any quantity has been sold (you may need to add a sold_quantity field to product_inventory table)
                if (isset($item->sold_quantity) && $item->sold_quantity > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete purchase. Some items from this purchase have been sold. Purchase deletion is not allowed once items are sold.'
                    ], 400);
                }
            }

            // Delete document if exists
            if ($purchase->document) {
                $documentPath = public_path('uploads/purchases/' . $purchase->document);
                if (file_exists($documentPath)) {
                    unlink($documentPath);
                }
            }

            // Note: Since we calculate supplier dues dynamically, we don't need to update supplier table
            // The dues will be recalculated automatically when needed
            
            // Delete inventory items (this will cascade)
            $purchase->inventoryItems()->delete();

            // Soft delete purchase (audit log will be created automatically by the trait)
            $purchase->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Receive stock for a purchase order
     */
    public function receiveStock(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found'
                ], 404);
            }

            if ($purchase->status !== 'ordered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase must be in ordered status to receive stock'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'inventory_items' => 'required|array|min:1',
                'inventory_items.*.product_id' => 'required|exists:products,id',
                'inventory_items.*.quantity' => 'required|integer|min:1',
                'inventory_items.*.purchase_price' => 'required|numeric|min:0',
                'inventory_items.*.tax_amount' => 'required|numeric|min:0',
                'inventory_items.*.profit_amount' => 'required|numeric|min:0',
                'inventory_items.*.one_item_amount' => 'required|numeric|min:0',
                'inventory_items.*.total_order_amount' => 'required|numeric|min:0',
                'inventory_items.*.batch_number' => 'nullable|string',
                'inventory_items.*.expiry_date' => 'nullable|date',
                'inventory_items.*.invoice_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create inventory items
            foreach ($request->inventory_items as $item) {
                ProductInventory::create([
                    'product_id' => $item['product_id'],
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $purchase->supplier_id,
                    'business_location_id' => $purchase->business_location_id,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['one_item_amount'],
                    'tax_amount' => $item['tax_amount'],
                    'profit_amount' => $item['profit_amount'],
                    'one_item_amount' => $item['one_item_amount'],
                    'total_order_amount' => $item['total_order_amount'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'invoice_number' => $item['invoice_number'] ?? null,
                    'inventory_type' => 'purchase',
                    'is_active' => true
                ]);
            }

            // Update purchase status to received
            $purchase->update(['status' => 'received']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock received successfully',
                'data' => $purchase->load(['supplier', 'businessLocation', 'createdBy', 'inventoryItems.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to receive stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get audit logs for a purchase
     */
    public function getAuditLogs($id)
    {
        try {
            $purchase = Purchase::withTrashed()->find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found'
                ], 404);
            }

            $auditLogs = $purchase->auditLogs()
                ->with('user:id,first_name,last_name,email')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $auditLogs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get audit logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier dues (calculated dynamically)
     */
    public function getSupplierDues(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplier = Supplier::find($request->supplier_id);
            
            // Calculate dues dynamically:
            // Initial dues (from registration) + All purchase amounts - All paid amounts
            $initialDues = $supplier->dues_amount ?? 0;
            $totalPurchases = Purchase::where('supplier_id', $request->supplier_id)->sum('total_amount');
            $totalPaid = Purchase::where('supplier_id', $request->supplier_id)->sum('paid_amount');
            
            $currentDues = $initialDues + $totalPurchases - $totalPaid;

            return response()->json([
                'success' => true,
                'data' => [
                    'supplier_id' => $supplier->id,
                    'initial_dues' => $initialDues,
                    'total_purchases' => $totalPurchases,
                    'total_paid' => $totalPaid,
                    'dues_amount' => max(0, $currentDues) // Ensure dues is never negative
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get supplier dues: ' . $e->getMessage()
            ], 500);
        }
    }
} 