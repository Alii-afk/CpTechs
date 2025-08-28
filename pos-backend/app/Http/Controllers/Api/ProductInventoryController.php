<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductInventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductInventoryController extends Controller
{
    /**
     * Display a listing of inventory items
     */
    public function index(Request $request)
    {
        try {
            $query = ProductInventory::with(['product', 'supplier', 'businessLocation', 'purchase']);

            // Apply filters
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('business_location_id')) {
                $query->where('business_location_id', $request->business_location_id);
            }

            if ($request->has('inventory_type')) {
                $query->where('inventory_type', $request->inventory_type);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search by product name or SKU
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_sku', 'like', "%{$search}%");
                });
            }

            $inventory = $query->orderBy('created_at', 'desc')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $inventory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current stock levels for all products
     */
    public function getStockLevels(Request $request)
    {
        try {
            $query = ProductInventory::selectRaw('
                product_id,
                SUM(quantity) as total_quantity,
                AVG(purchase_price) as avg_purchase_price,
                AVG(selling_price) as avg_selling_price
            ')
            ->with(['product'])
            ->where('is_active', true)
            ->groupBy('product_id');

            if ($request->has('business_location_id')) {
                $query->where('business_location_id', $request->business_location_id);
            }

            $stockLevels = $query->get();

            return response()->json([
                'success' => true,
                'data' => $stockLevels
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stock levels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStock(Request $request)
    {
        try {
            // Get all products with their current stock levels
            $stockLevels = ProductInventory::selectRaw('
                product_inventory.product_id,
                SUM(product_inventory.quantity) as total_quantity
            ')
            ->with(['product'])
            ->where('product_inventory.is_active', true)
            ->groupBy('product_inventory.product_id')
            ->get();

            // Filter for low stock products
            $lowStock = $stockLevels->filter(function ($item) {
                return $item->total_quantity <= $item->product->default_alert_quantity;
            });

            return response()->json([
                'success' => true,
                'data' => $lowStock->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch low stock products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStock(Request $request)
    {
        try {
            $query = ProductInventory::selectRaw('
                product_id,
                SUM(quantity) as total_quantity
            ')
            ->with(['product'])
            ->where('is_active', true)
            ->groupBy('product_id')
            ->havingRaw('SUM(quantity) = 0');

            if ($request->has('business_location_id')) {
                $query->where('business_location_id', $request->business_location_id);
            }

            $outOfStock = $query->get();

            return response()->json([
                'success' => true,
                'data' => $outOfStock
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch out of stock products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified inventory item
     */
    public function show($id)
    {
        try {
            $inventory = ProductInventory::with(['product', 'supplier', 'businessLocation', 'purchase'])
                ->find($id);

            if (!$inventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory item not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $inventory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified inventory item
     */
    public function update(Request $request, $id)
    {
        try {
            $inventory = ProductInventory::find($id);

            if (!$inventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory item not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'sometimes|integer|min:0',
                'purchase_price' => 'sometimes|numeric|min:0',
                'selling_price' => 'sometimes|numeric|min:0',
                'inclusive_tax_rate' => 'sometimes|numeric|min:0|max:100',
                'exclusive_tax_rate' => 'sometimes|numeric|min:0|max:100',
                'profit_margin' => 'sometimes|numeric|min:0|max:100',
                'tax_amount' => 'sometimes|numeric|min:0',
                'profit_amount' => 'sometimes|numeric|min:0',
                'one_item_amount' => 'sometimes|numeric|min:0',
                'total_order_amount' => 'sometimes|numeric|min:0',
                'batch_number' => 'sometimes|string|max:255',
                'expiry_date' => 'sometimes|date',
                'invoice_number' => 'sometimes|string|max:255',
                'notes' => 'sometimes|string',
                'is_active' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $inventory->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Inventory item updated successfully',
                'data' => $inventory->load(['product', 'supplier', 'businessLocation', 'purchase'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified inventory item
     */
    public function destroy($id)
    {
        try {
            $inventory = ProductInventory::find($id);

            if (!$inventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory item not found'
                ], 404);
            }

            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest pricing for a product from inventory
     */
    public function getLatestPricing(Product $product)
    {
        try {
            // Get the most recent inventory entry for this product
            $latestInventory = ProductInventory::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestInventory) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No inventory found for this product'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'purchase_price' => $latestInventory->purchase_price,
                    'selling_price' => $latestInventory->selling_price,
                    'inclusive_tax_rate' => $latestInventory->inclusive_tax_rate,
                    'exclusive_tax_rate' => $latestInventory->exclusive_tax_rate,
                    'profit_margin' => $latestInventory->profit_margin,
                    'tax_amount' => $latestInventory->tax_amount,
                    'profit_amount' => $latestInventory->profit_amount,
                    'one_item_amount' => $latestInventory->one_item_amount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch latest pricing: ' . $e->getMessage()
            ], 500);
        }
    }
} 