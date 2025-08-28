<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $customers = Customer::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers',
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
                'email' => 'required|email|unique:customers,email',
                'business_website' => 'nullable|url|max:255',
                'whatsapp' => 'nullable|string|max:255',
                'business_location' => 'nullable|string|max:255',
                'business_status' => 'required|in:Shop,Website,Dealer',
                'date_of_enrollment' => 'nullable|date',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'address' => 'nullable|string',
                'shipping_address' => 'nullable|string',
                'is_public_profile' => 'boolean',
                'credit_limit' => 'nullable|numeric|min:0',
                'current_balance' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customerData = $validator->validated();
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/customers'), $safeName);
                $customerData['profile_image'] = $safeName;
            }
            
            // Set default values
            $customerData['is_public_profile'] = $customerData['is_public_profile'] ?? false;
            $customerData['credit_limit'] = $customerData['credit_limit'] ?? 0.00;
            $customerData['current_balance'] = $customerData['current_balance'] ?? 0.00;

            $customer = Customer::create($customerData);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $customer->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            // Debug: Log incoming request data
            Log::info('Customer update request received', [
                'customer_id' => $customer->id,
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
                                if (in_array($fieldName, ['credit_limit', 'current_balance'])) {
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
                    Rule::unique('customers')->ignore($customer->id)
                ],
                'business_website' => 'nullable|url|max:255',
                'whatsapp' => 'nullable|string|max:255',
                'business_location' => 'nullable|string|max:255',
                'business_status' => 'sometimes|required|in:Shop,Website,Dealer',
                'date_of_enrollment' => 'nullable|date',
                'address' => 'nullable|string',
                'shipping_address' => 'nullable|string',
                'is_public_profile' => 'boolean',
                'credit_limit' => 'nullable|numeric|min:0',
                'current_balance' => 'nullable|numeric|min:0'
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

            $customerData = $validator->validated();
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/customers'), $safeName);
                $customerData['profile_image'] = $safeName;
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
                                
                                file_put_contents(public_path('uploads/customers/' . $safeName), $fileContent);
                                $customerData['profile_image'] = $safeName;
                                break;
                            }
                        }
                    }
                }
            }
            
            $customer->update($customerData);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            // Delete profile image if exists
            if ($customer->profile_image) {
                $imagePath = public_path('uploads/customers/' . $customer->profile_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update customer balance
     */
    public function updateBalance(Request $request, Customer $customer)
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
                    $customer->addToBalance($amount);
                    break;
                case 'subtract':
                    $customer->subtractFromBalance($amount);
                    break;
                case 'set':
                    $customer->current_balance = max(0, $amount);
                    $customer->save();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer balance updated successfully',
                'data' => [
                    'customer_id' => $customer->id,
                    'current_balance' => $customer->current_balance,
                    'formatted_current_balance' => $customer->formatted_current_balance,
                    'available_credit' => $customer->available_credit,
                    'formatted_available_credit' => $customer->formatted_available_credit
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers for dropdowns
     */
    public function list()
    {
        try {
            $customers = Customer::select('id', 'first_name', 'last_name', 'business_name', 'email', 'contact_type')
                ->orderBy('business_name')
                ->orderBy('first_name')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->display_name,
                        'email' => $customer->email
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers with outstanding balances
     */
    public function withOutstandingBalance()
    {
        try {
            $customers = Customer::where('current_balance', '>', 0)
                ->orderBy('current_balance', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers with outstanding balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
