<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Added Log facade

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $suppliers = Supplier::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers',
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
            // Convert string boolean values to actual booleans
            $data = $request->all();
            if (isset($data['is_public_profile'])) {
                $data['is_public_profile'] = filter_var($data['is_public_profile'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $validator = Validator::make($data, [
                'contact_type' => 'required|in:Individual,Business',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'business_name' => 'nullable|string|max:255',
                'email' => 'required|email|unique:suppliers,email',
                'business_website' => 'nullable|url|max:255',
                'whatsapp' => 'nullable|string|max:255',
                'business_location' => 'nullable|string|max:255',
                'business_status' => 'required|in:Shop,Website,Dealer',
                'date_of_enrollment' => 'nullable|date',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'address' => 'nullable|string',
                'is_public_profile' => 'boolean',
                'dues_amount' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplierData = $validator->validated();
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/suppliers'), $safeName);
                $supplierData['profile_image'] = $safeName;
            }
            
            // Set default values
            $supplierData['is_public_profile'] = $supplierData['is_public_profile'] ?? false;
            $supplierData['dues_amount'] = $supplierData['dues_amount'] ?? 0.00;

            $supplier = Supplier::create($supplierData);

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully',
                'data' => $supplier
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $supplier->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        try {
            // Debug: Log incoming request data
            Log::info('Supplier update request received', [
                'supplier_id' => $supplier->id,
                'request_data' => $request->all(),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'raw_input' => $request->getContent()
            ]);
            
            // Parse FormData manually for PUT requests
            $data = [];
            if ($request->method() === 'PUT' && strpos($request->header('Content-Type'), 'multipart/form-data') !== false) {
                // Parse the raw input manually
                $rawInput = $request->getContent();
                $boundary = '----WebKitFormBoundary' . substr($rawInput, strpos($rawInput, 'WebKitFormBoundary') + 18, 16);
                $parts = explode($boundary, $rawInput);
                
                foreach ($parts as $part) {
                    if (strpos($part, 'Content-Disposition: form-data') !== false) {
                        // Extract field name and value
                        if (preg_match('/name="([^"]+)"/', $part, $nameMatch)) {
                            $fieldName = $nameMatch[1];
                            // Extract value (everything after the double newline)
                            $valueStart = strpos($part, "\r\n\r\n");
                            if ($valueStart !== false) {
                                $value = substr($part, $valueStart + 4);
                                // Remove trailing \r\n and boundary markers
                                $value = preg_replace('/\r\n--.*$/', '', $value);
                                $value = rtrim($value, "\r\n");
                                
                                // Skip empty profile_image field
                                if ($fieldName === 'profile_image' && $value === '') {
                                    continue;
                                }
                                
                                // Handle profile_image field - check if it's a real file upload
                                if ($fieldName === 'profile_image') {
                                    // Check if this is a real file upload by looking for file content
                                    if (strpos($part, 'Content-Type:') !== false && strpos($part, 'image/') !== false) {
                                        // This is a real file upload, let Laravel handle it
                                        continue;
                                    } else {
                                        // This is not a real file, skip it
                                        continue;
                                    }
                                }
                                
                                // Data type conversion
                                if (in_array($fieldName, ['dues_amount'])) {
                                    $data[$fieldName] = $value === '' ? null : (float) $value;
                                } elseif (in_array($fieldName, ['is_public_profile'])) {
                                    $data[$fieldName] = $value === 'true';
                                } else {
                                    $data[$fieldName] = $value === '' ? null : $value;
                                }
                            }
                        }
                    }
                }
                Log::info('Parsed FormData manually', ['parsed_data' => $data]);
            } else {
                $data = $request->all();
            }

            // Convert string boolean values to actual booleans (only if not using manual parsing)
            if (!($request->method() === 'PUT' && strpos($request->header('Content-Type'), 'multipart/form-data') !== false)) {
                if (isset($data['is_public_profile'])) {
                    $data['is_public_profile'] = filter_var($data['is_public_profile'], FILTER_VALIDATE_BOOLEAN);
                }
            }
            
            $validationRules = [
                'contact_type' => 'sometimes|required|in:Individual,Business',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'business_name' => 'nullable|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('suppliers')->ignore($supplier->id)
                ],
                'business_website' => 'nullable|url|max:255',
                'whatsapp' => 'nullable|string|max:255',
                'business_location' => 'nullable|string|max:255',
                'business_status' => 'sometimes|required|in:Shop,Website,Dealer',
                'date_of_enrollment' => 'nullable|date',
                'address' => 'nullable|string',
                'is_public_profile' => 'boolean',
                'dues_amount' => 'nullable|numeric|min:0'
            ];

            // Add profile_image validation only if not using manual parsing
            if (!$request->method() === 'PUT' || strpos($request->header('Content-Type'), 'multipart/form-data') === false) {
                $validationRules['profile_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
            }

            $validator = Validator::make($data, $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplierData = $validator->validated();
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/suppliers'), $safeName);
                $supplierData['profile_image'] = $safeName;
            } elseif ($request->method() === 'PUT' && strpos($request->header('Content-Type'), 'multipart/form-data') !== false) {
                // Check if there's a file upload in the raw input for manual parsing
                $rawInput = $request->getContent();
                if (strpos($rawInput, 'Content-Type: image/') !== false) {
                    // Extract file from raw input
                    $boundary = '----WebKitFormBoundary' . substr($rawInput, strpos($rawInput, 'WebKitFormBoundary') + 18, 16);
                    $parts = explode($boundary, $rawInput);
                    
                    foreach ($parts as $part) {
                        if (strpos($part, 'Content-Disposition: form-data; name="profile_image"') !== false && 
                            strpos($part, 'Content-Type: image/') !== false) {
                            
                            // Extract filename
                            if (preg_match('/filename="([^"]+)"/', $part, $filenameMatch)) {
                                $originalName = $filenameMatch[1];
                                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                                
                                // Extract file content and save it
                                $fileStart = strpos($part, "\r\n\r\n") + 4;
                                $fileEnd = strrpos($part, "\r\n");
                                $fileContent = substr($part, $fileStart, $fileEnd - $fileStart);
                                
                                file_put_contents(public_path('uploads/suppliers/' . $safeName), $fileContent);
                                $supplierData['profile_image'] = $safeName;
                                break;
                            }
                        }
                    }
                }
            }
            
            $supplier->update($supplierData);

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully',
                'data' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier dues
     */
    public function getDues(Supplier $supplier)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->display_name,
                    'dues_amount' => $supplier->dues_amount,
                    'formatted_dues' => $supplier->formatted_dues,
                    'has_dues' => $supplier->hasDues()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supplier dues',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update supplier dues
     */
    public function updateDues(Request $request, Supplier $supplier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
                'operation' => 'required|in:add,subtract,set'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $amount = $request->amount;
            $operation = $request->operation;

            switch ($operation) {
                case 'add':
                    $supplier->addDues($amount);
                    break;
                case 'subtract':
                    $supplier->subtractDues($amount);
                    break;
                case 'set':
                    $supplier->dues_amount = max(0, $amount);
                    $supplier->save();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier dues updated successfully',
                'data' => [
                    'supplier_id' => $supplier->id,
                    'dues_amount' => $supplier->dues_amount,
                    'formatted_dues' => $supplier->formatted_dues
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier dues',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active suppliers for dropdowns
     */
    public function active()
    {
        try {
            $suppliers = Supplier::active()
                ->select('id', 'first_name', 'last_name', 'business_name', 'email', 'contact_type')
                ->orderBy('business_name')
                ->orderBy('first_name')
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->display_name,
                        'email' => $supplier->email
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active suppliers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers with dues
     */
    public function withDues()
    {
        try {
            $suppliers = Supplier::where('dues_amount', '>', 0)
                ->orderBy('dues_amount', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers with dues',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
