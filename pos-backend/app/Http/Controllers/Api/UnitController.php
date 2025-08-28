<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    /**
     * Display a listing of units
     */
    public function index()
    {
        try {
            $units = Unit::ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => $units
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch units: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Store a newly created unit
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'unit_name' => 'required|string|max:255|unique:units',
                'unit_code' => 'nullable|string|max:20|unique:units',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unit code if not provided
            $unitCode = $request->unit_code;
            if (!$unitCode) {
                $unitCode = Unit::generateUnitCode($request->unit_name);
            }

            // Create unit
            $unit = Unit::create([
                'unit_name' => $request->unit_name,
                'unit_code' => $unitCode,
                'description' => $request->description,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully',
                'data' => $unit
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified unit
     */
    public function show($id)
    {
        try {
            $unit = Unit::find($id);
            
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $unit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified unit
     */
    public function update(Request $request, $id)
    {
        try {
            $unit = Unit::find($id);
            
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'unit_name' => 'required|string|max:255|unique:units,unit_name,' . $id,
                'unit_code' => 'nullable|string|max:20|unique:units,unit_code,' . $id,
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unit code if not provided
            $unitCode = $request->unit_code;
            if (!$unitCode) {
                $unitCode = Unit::generateUnitCode($request->unit_name);
            }

            // Update unit
            $unit->update([
                'unit_name' => $request->unit_name,
                'unit_code' => $unitCode,
                'description' => $request->description,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully',
                'data' => $unit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified unit
     */
    public function destroy($id)
    {
        try {
            $unit = Unit::find($id);
            
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit not found'
                ], 404);
            }

            // Delete unit
            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unit deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active units only
     */
    public function active()
    {
        try {
            $units = Unit::active()->ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => $units
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active units: ' . $e->getMessage()
            ], 500);
        }
    }
}
