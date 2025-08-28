<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories
     */
    public function index()
    {
        try {
            $categories = ProductCategory::ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product category
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|string|max:255|unique:product_categories',
                'category_description' => 'nullable|string',
                'category_status' => 'required|in:active,inactive',
                'category_code' => 'nullable|string|max:20|unique:product_categories'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate category code if not provided
            $categoryCode = $request->category_code;
            if (!$categoryCode) {
                $categoryCode = ProductCategory::generateCategoryCode($request->category_name);
            }

            // Create category
            $category = ProductCategory::create([
                'category_name' => $request->category_name,
                'category_description' => $request->category_description,
                'category_status' => $request->category_status,
                'category_code' => $categoryCode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product category
     */
    public function show($id)
    {
        try {
            $category = ProductCategory::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product category
     */
    public function update(Request $request, $id)
    {
        try {
            $category = ProductCategory::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'category_name' => 'required|string|max:255|unique:product_categories,category_name,' . $id,
                'category_description' => 'nullable|string',
                'category_status' => 'required|in:active,inactive',
                'category_code' => 'nullable|string|max:20|unique:product_categories,category_code,' . $id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate category code if not provided
            $categoryCode = $request->category_code;
            if (!$categoryCode) {
                $categoryCode = ProductCategory::generateCategoryCode($request->category_name);
            }

            // Update category
            $category->update([
                'category_name' => $request->category_name,
                'category_description' => $request->category_description,
                'category_status' => $request->category_status,
                'category_code' => $categoryCode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product category
     */
    public function destroy($id)
    {
        try {
            $category = ProductCategory::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Delete category
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active categories only
     */
    public function active()
    {
        try {
            $categories = ProductCategory::active()->ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active categories: ' . $e->getMessage()
            ], 500);
        }
    }
} 