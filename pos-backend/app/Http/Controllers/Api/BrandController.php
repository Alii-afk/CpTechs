<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Flavor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of brands with their flavors
     */
    public function index()
    {
        try {
            $brands = Brand::with('flavors')->get();
            
            return response()->json([
                'success' => true,
                'data' => $brands
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created brand with flavors
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'brand_name' => 'required|string|max:255|unique:brands,brand_name',
                'brand_description' => 'nullable|string',
                'brand_status' => 'required|in:active,inactive',
                'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'flavors' => 'nullable|array',
                'flavors.*.flavor_name' => 'required|string|max:255',
                'flavors.*.flavor_description' => 'nullable|string',
                'flavors.*.flavor_status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle brand logo upload
            $brandLogoPath = null;
            if ($request->hasFile('brand_logo')) {
                $brandLogoPath = $request->file('brand_logo')->store('brands', 'public');
            }

            // Create brand
            $brand = Brand::create([
                'brand_name' => $request->brand_name,
                'brand_description' => $request->brand_description,
                'brand_status' => $request->brand_status,
                'brand_logo' => $brandLogoPath
            ]);

            // Create flavors if provided
            if ($request->has('flavors') && is_array($request->flavors)) {
                foreach ($request->flavors as $flavorData) {
                    Flavor::create([
                        'brand_id' => $brand->id,
                        'flavor_name' => $flavorData['flavor_name'],
                        'flavor_description' => $flavorData['flavor_description'] ?? null,
                        'flavor_status' => $flavorData['flavor_status']
                    ]);
                }
            }

            // Load the brand with flavors
            $brand->load('flavors');

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully',
                'data' => $brand
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified brand with flavors
     */
    public function show($id)
    {
        try {
            $brand = Brand::with('flavors')->find($id);
            
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $brand
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, $id)
    {
        try {
            $brand = Brand::find($id);
            
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'brand_name' => 'required|string|max:255|unique:brands,brand_name,' . $id,
                'brand_description' => 'nullable|string',
                'brand_status' => 'required|in:active,inactive',
                'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle brand logo upload
            $brandLogoPath = $brand->brand_logo;
            if ($request->hasFile('brand_logo')) {
                // Delete old logo if exists
                if ($brand->brand_logo) {
                    Storage::disk('public')->delete($brand->brand_logo);
                }
                $brandLogoPath = $request->file('brand_logo')->store('brands', 'public');
            }

            // Update brand
            $brand->update([
                'brand_name' => $request->brand_name,
                'brand_description' => $request->brand_description,
                'brand_status' => $request->brand_status,
                'brand_logo' => $brandLogoPath
            ]);

            // Load the brand with flavors
            $brand->load('flavors');

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully',
                'data' => $brand
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified brand
     */
    public function destroy($id)
    {
        try {
            $brand = Brand::find($id);
            
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            // Delete brand logo if exists
            if ($brand->brand_logo) {
                Storage::disk('public')->delete($brand->brand_logo);
            }

            // Delete brand (flavors will be deleted automatically due to cascade)
            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active brands only
     */
    public function active()
    {
        try {
            $brands = Brand::where('brand_status', 'active')
                          ->with(['flavors' => function($query) {
                              $query->where('flavor_status', 'active');
                          }])
                          ->get();
            
            return response()->json([
                'success' => true,
                'data' => $brands
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active brands: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get brands with flavor count
     */
    public function withFlavorCount()
    {
        try {
            $brands = Brand::withCount('flavors')->with('flavors')->get();
            
            return response()->json([
                'success' => true,
                'data' => $brands
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands: ' . $e->getMessage()
            ], 500);
        }
    }
}
