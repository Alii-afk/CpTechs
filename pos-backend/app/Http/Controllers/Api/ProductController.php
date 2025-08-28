<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use App\Models\Flavor;
use App\Models\Unit;
use App\Models\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'brand', 'flavor', 'unit', 'createdBy']);

            // Apply filters

            if ($request->has('category_id')) {
                $query->byCategory($request->category_id);
            }

            if ($request->has('brand_id')) {
                $query->byBrand($request->brand_id);
            }

            if ($request->has('product_type')) {
                $query->where('product_type', $request->product_type);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('is_public')) {
                $query->where('is_public', $request->boolean('is_public'));
            }

            if ($request->has('created_by')) {
                $query->where('created_by', $request->created_by);
            }

            // Search by name or SKU
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_sku', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('product_name')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'product_sku' => 'nullable|string|max:50|unique:products',
                'product_description' => 'nullable|string',
                'product_category_id' => 'required|exists:product_categories,id',
                'brand_id' => 'required|exists:brands,id',
                'flavor_id' => 'nullable|exists:flavors,id',
                'unit_id' => 'required|exists:units,id',

                'default_alert_quantity' => 'required|integer|min:0',
                'default_purchase_price' => 'required|numeric|min:0',
                'default_selling_price' => 'required|numeric|min:0',
                'default_inclusive_tax_rate' => 'required|numeric|min:0|max:100',
                'default_exclusive_tax_rate' => 'required|numeric|min:0|max:100',
                'default_profit_margin' => 'required|numeric|min:0|max:100',
                'product_expiry' => 'nullable|date',
                'stop_selling_days' => 'required|integer|min:0',
                'is_active' => 'boolean',
                'is_public' => 'boolean',
                'product_type' => 'required|in:physical,digital,service',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate SKU if not provided
            $sku = $request->product_sku;
            if (!$sku) {
                $brand = Brand::find($request->brand_id);
                $sku = Product::generateSku($request->product_name, $brand->brand_name);
            }

            $productData = $request->except(['product_image']);
            $productData['product_sku'] = $sku;
            $productData['created_by'] = 4; // Using existing user ID - in production this would come from authentication

            // Handle product image upload
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/products'), $safeName);
                $productData['product_image'] = $safeName;
            }

            $product = Product::create($productData);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'brand', 'flavor', 'unit', 'createdBy'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        try {
            $product = Product::with(['category', 'brand', 'flavor', 'unit', 'inventory.supplier', 'createdBy'])
                ->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'product_sku' => 'nullable|string|max:50|unique:products,product_sku,' . $id,
                'product_description' => 'nullable|string',
                'product_category_id' => 'required|exists:product_categories,id',
                'brand_id' => 'required|exists:brands,id',
                'flavor_id' => 'nullable|exists:flavors,id',
                'unit_id' => 'required|exists:units,id',
                'default_alert_quantity' => 'required|integer|min:0',
                'default_purchase_price' => 'required|numeric|min:0',
                'default_selling_price' => 'required|numeric|min:0',
                'default_inclusive_tax_rate' => 'required|numeric|min:0|max:100',
                'default_exclusive_tax_rate' => 'required|numeric|min:0|max:100',
                'default_profit_margin' => 'required|numeric|min:0|max:100',
                'product_expiry' => 'nullable|date',
                'stop_selling_days' => 'required|integer|min:0',
                'is_active' => 'boolean',
                'is_public' => 'boolean',
                'product_type' => 'required|in:physical,digital,service',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $productData = $request->except(['product_image']);

            // Handle product image upload
            if ($request->hasFile('product_image')) {
                // Delete old image if exists
                if ($product->product_image) {
                    $oldImagePath = public_path('uploads/products/' . $product->product_image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $file = $request->file('product_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/products'), $safeName);
                $productData['product_image'] = $safeName;
            }

            $product->update($productData);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'brand', 'flavor', 'unit', 'createdBy'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if product has any inventory records
            $inventoryCount = $product->inventory()->count();
            if ($inventoryCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete product. It has {$inventoryCount} inventory record(s) associated with it. Please remove inventory records first."
                ], 400);
            }

            // Delete the product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint errors
            if ($e->getCode() == 23000) { // MySQL foreign key constraint error
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product. It is being used by other records in the system.'
                ], 400);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products for dropdown/select options
     */
    public function getOptions()
    {
        try {
            $products = Product::active()
                ->select('id', 'product_name', 'product_sku', 'brand_id', 'flavor_id')
                ->with(['brand', 'flavor'])
                ->orderBy('product_name')
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'text' => $product->full_name . ' (SKU: ' . $product->product_sku . ')',
                        'sku' => $product->product_sku,
                        'brand' => $product->brand->brand_name ?? '',
                        'flavor' => $product->flavor->flavor_name ?? ''
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStock()
    {
        try {
            $products = Product::with(['category', 'brand', 'unit', 'businessLocation'])
                ->get()
                ->filter(function($product) {
                    return $product->isLowStock();
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $products
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
    public function getOutOfStock()
    {
        try {
            $products = Product::with(['category', 'brand', 'unit', 'businessLocation'])
                ->get()
                ->filter(function($product) {
                    return $product->isOutOfStock();
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch out of stock products: ' . $e->getMessage()
            ], 500);
        }
    }
} 