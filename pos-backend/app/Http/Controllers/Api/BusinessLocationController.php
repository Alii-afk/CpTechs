<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessLocation;
use Illuminate\Support\Facades\Validator;

class BusinessLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $businessLocations = BusinessLocation::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $businessLocations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'business_name' => 'required|string|max:255',
                'landmark' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'zip_code' => 'required|string|max:20',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|unique:business_locations,email',
                'website' => 'nullable|url|max:255',
                'business_currency' => 'required|in:usd,eur,gbp,pkr,aed',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unique location ID
            $locationId = BusinessLocation::generateLocationId();

            $businessLocation = BusinessLocation::create([
                'business_name' => $request->business_name,
                'location_id' => $locationId,
                'landmark' => $request->landmark,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'state' => $request->state,
                'country' => $request->country,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'website' => $request->website,
                'business_currency' => $request->business_currency,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business location created successfully',
                'data' => $businessLocation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create business location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $businessLocation = BusinessLocation::find($id);
            
            if (!$businessLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business location not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $businessLocation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $businessLocation = BusinessLocation::find($id);
            
            if (!$businessLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business location not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'business_name' => 'required|string|max:255',
                'landmark' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'zip_code' => 'required|string|max:20',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|unique:business_locations,email,' . $id,
                'website' => 'nullable|url|max:255',
                'business_currency' => 'required|in:usd,eur,gbp,pkr,aed',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $businessLocation->update([
                'business_name' => $request->business_name,
                'landmark' => $request->landmark,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'state' => $request->state,
                'country' => $request->country,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'website' => $request->website,
                'business_currency' => $request->business_currency,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business location updated successfully',
                'data' => $businessLocation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update business location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $businessLocation = BusinessLocation::find($id);
            
            if (!$businessLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business location not found'
                ], 404);
            }

            // Check if there are users associated with this location
            if ($businessLocation->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete business location. There are users associated with this location.'
                ], 400);
            }

            $businessLocation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Business location deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete business location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active business locations for dropdown
     */
    public function active()
    {
        try {
            $businessLocations = BusinessLocation::active()
                ->select('id', 'business_name', 'location_id', 'city', 'state', 'business_currency')
                ->orderBy('business_name')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $businessLocations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active business locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
