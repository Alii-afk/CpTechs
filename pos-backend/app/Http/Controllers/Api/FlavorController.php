<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Flavor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlavorController extends Controller
{
    /**
     * Display a listing of flavors
     */
    public function index()
    {
        try {
            $flavors = Flavor::with('brand')->get();
            
            return response()->json([
                'success' => true,
                'data' => $flavors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flavors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created flavor for a brand
     */
    public function store(Request $request, $brandId)
    {
        try {
            // Check if brand exists
            $brand = Brand::find($brandId);
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'flavor_name' => 'required|string|max:255|unique:flavors,flavor_name,NULL,id,brand_id,' . $brandId,
                'flavor_description' => 'nullable|string',
                'flavor_status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $flavor = Flavor::create([
                'brand_id' => $brandId,
                'flavor_name' => $request->flavor_name,
                'flavor_description' => $request->flavor_description,
                'flavor_status' => $request->flavor_status
            ]);

            // Load the brand relationship
            $flavor->load('brand');

            return response()->json([
                'success' => true,
                'message' => 'Flavor created successfully',
                'data' => $flavor
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create flavor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified flavor
     */
    public function show($id)
    {
        try {
            $flavor = Flavor::with('brand')->find($id);
            
            if (!$flavor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Flavor not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $flavor
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flavor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified flavor
     */
    public function update(Request $request, $id)
    {
        try {
            $flavor = Flavor::find($id);
            
            if (!$flavor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Flavor not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'flavor_name' => 'required|string|max:255|unique:flavors,flavor_name,' . $id . ',id,brand_id,' . $flavor->brand_id,
                'flavor_description' => 'nullable|string',
                'flavor_status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $flavor->update([
                'flavor_name' => $request->flavor_name,
                'flavor_description' => $request->flavor_description,
                'flavor_status' => $request->flavor_status
            ]);

            // Load the brand relationship
            $flavor->load('brand');

            return response()->json([
                'success' => true,
                'message' => 'Flavor updated successfully',
                'data' => $flavor
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update flavor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified flavor
     */
    public function destroy($id)
    {
        try {
            $flavor = Flavor::find($id);
            
            if (!$flavor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Flavor not found'
                ], 404);
            }

            $flavor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Flavor deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete flavor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flavors by brand
     */
    public function byBrand($brandId)
    {
        try {
            $brand = Brand::find($brandId);
            
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            $flavors = $brand->flavors;

            return response()->json([
                'success' => true,
                'data' => $flavors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flavors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active flavors by brand
     */
    public function activeByBrand($brandId)
    {
        try {
            $brand = Brand::find($brandId);
            
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            $flavors = $brand->activeFlavors;

            return response()->json([
                'success' => true,
                'data' => $flavors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active flavors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active flavors
     */
    public function active()
    {
        try {
            $flavors = Flavor::active()->with('brand')->get();
            
            return response()->json([
                'success' => true,
                'data' => $flavors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active flavors: ' . $e->getMessage()
            ], 500);
        }
    }
}
