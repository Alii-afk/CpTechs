<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CommissionAgent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with(['userRole', 'businessLocation', 'commissionAgent'])
                ->orderBy('created_at', 'desc')
                ->get();
        
        return response()->json([
                'success' => true,
            'data' => $users
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
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
            if (isset($data['is_commission_agent'])) {
                $data['is_commission_agent'] = filter_var($data['is_commission_agent'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $validator = Validator::make($data, [
                'username' => 'nullable|string|max:255|unique:users,username',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:Male,Female',
            'password' => 'required|string|min:8',
                'user_role_id' => 'required|exists:user_roles,id',
                'business_location_id' => 'nullable|exists:business_locations,id',
                'date_of_joining' => 'nullable|date',
                'contract_time' => 'nullable|date',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'address' => 'nullable|string',
                'is_public_profile' => 'boolean',
                'is_commission_agent' => 'boolean',
                'basic_salary' => 'nullable|numeric|min:0',
                'medical_allowance' => 'nullable|numeric|min:0',
                'house_allowance' => 'nullable|numeric|min:0',
                'food_allowance' => 'nullable|numeric|min:0',
                'travel_allowance' => 'nullable|numeric|min:0',
                'security' => 'nullable|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0',
                'total_salary' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userData = $validator->validated();
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/users'), $safeName);
                $userData['profile_image'] = $safeName;
            }
            
            $userData['password'] = Hash::make($request->password);
            
            // Calculate total salary if not provided
            if (!isset($userData['total_salary'])) {
                $userData['total_salary'] = 0;
                $userData['total_salary'] += $userData['basic_salary'] ?? 0;
                $userData['total_salary'] += $userData['medical_allowance'] ?? 0;
                $userData['total_salary'] += $userData['house_allowance'] ?? 0;
                $userData['total_salary'] += $userData['food_allowance'] ?? 0;
                $userData['total_salary'] += $userData['travel_allowance'] ?? 0;
                $userData['total_salary'] += $userData['security'] ?? 0;
                $userData['total_salary'] += $userData['bonus'] ?? 0;
            }

            $user = User::create($userData);

            // If user is marked as commission agent, create commission agent record
            if ($request->boolean('is_commission_agent') && $request->has('commission_data')) {
                $commissionValidator = Validator::make($request->commission_data, [
                    'sale_target' => 'required|numeric|min:0',
                    'target_period' => 'required|in:daily,weekly,monthly,yearly',
                    'commission_percentage' => 'required|numeric|min:0|max:100',
                ]);

                if (!$commissionValidator->fails()) {
                    CommissionAgent::create([
                        'user_id' => $user->id,
                        'sale_target' => $request->commission_data['sale_target'],
                        'target_period' => $request->commission_data['target_period'],
                        'commission_percentage' => $request->commission_data['commission_percentage'],
                    ]);
                }
            }

        return response()->json([
                'success' => true,
            'message' => 'User created successfully',
                'data' => $user->load(['userRole', 'businessLocation', 'commissionAgent'])
        ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
        return response()->json([
                'success' => true,
                'data' => $user->load(['userRole', 'businessLocation', 'commissionAgent'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            // Debug: Log incoming request data
            Log::info('User update request received', [
                'user_id' => $user->id,
                'request_data' => $request->all(),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'has_commission_agent' => $request->has('commission_agent'),
                'is_commission_agent' => $request->get('is_commission_agent'),
                'boolean_is_commission_agent' => $request->boolean('is_commission_agent'),
                'raw_input' => $request->getContent(),
                'post_data' => $request->post(),
                'input_data' => $request->input()
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
                                
                                // Convert data types based on field name
                                if (in_array($fieldName, ['basic_salary', 'medical_allowance', 'house_allowance', 'food_allowance', 'travel_allowance', 'security', 'bonus', 'total_salary'])) {
                                    $data[$fieldName] = $value === '' ? null : (float) $value;
                                } elseif (in_array($fieldName, ['business_location_id', 'user_role_id'])) {
                                    $data[$fieldName] = $value === '' ? null : (int) $value;
                                } elseif (in_array($fieldName, ['is_public_profile', 'is_commission_agent'])) {
                                    $data[$fieldName] = $value === 'true';
                                } elseif (strpos($fieldName, 'commission_agent[') === 0) {
                                    // Handle commission agent nested fields
                                    $nestedField = str_replace(['commission_agent[', ']'], '', $fieldName);
                                    if (!isset($data['commission_agent'])) {
                                        $data['commission_agent'] = [];
                                    }
                                    $data['commission_agent'][$nestedField] = $value === '' ? null : $value;
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
            
            // Convert string boolean values to actual booleans (only for non-manual parsing)
            if (!$request->method() === 'PUT' || strpos($request->header('Content-Type'), 'multipart/form-data') === false) {
                if (isset($data['is_public_profile'])) {
                    $data['is_public_profile'] = filter_var($data['is_public_profile'], FILTER_VALIDATE_BOOLEAN);
                }
                if (isset($data['is_commission_agent'])) {
                    $data['is_commission_agent'] = filter_var($data['is_commission_agent'], FILTER_VALIDATE_BOOLEAN);
                }
            }
            
            // Build validation rules
            $validationRules = [
                'username' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
                'phone' => 'nullable|string|max:255',
                'gender' => 'nullable|in:Male,Female',
            'password' => 'sometimes|required|string|min:8',
                'user_role_id' => 'sometimes|required|exists:user_roles,id',
                'business_location_id' => 'nullable|exists:business_locations,id',
                'date_of_joining' => 'nullable|date',
                'contract_time' => 'nullable|date',
                'address' => 'nullable|string',
                'is_public_profile' => 'boolean',
                'is_commission_agent' => 'boolean',
                'basic_salary' => 'nullable|numeric|min:0',
                'medical_allowance' => 'nullable|numeric|min:0',
                'house_allowance' => 'nullable|numeric|min:0',
                'food_allowance' => 'nullable|numeric|min:0',
                'travel_allowance' => 'nullable|numeric|min:0',
                'security' => 'nullable|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0',
                'total_salary' => 'nullable|numeric|min:0',
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

            $userData = $validator->validated();
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $file->move(public_path('uploads/users'), $safeName);
                $userData['profile_image'] = $safeName;
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
                                
                                file_put_contents(public_path('uploads/users/' . $safeName), $fileContent);
                                $userData['profile_image'] = $safeName;
                                break;
                            }
                        }
                    }
                }
            }
        
        if ($request->has('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            // Calculate total salary if salary fields are updated
            if (isset($data['basic_salary']) || isset($data['medical_allowance']) || isset($data['house_allowance']) || 
                isset($data['food_allowance']) || isset($data['travel_allowance']) || isset($data['security']) || isset($data['bonus'])) {
                $userData['total_salary'] = 0;
                $userData['total_salary'] += $userData['basic_salary'] ?? $user->basic_salary ?? 0;
                $userData['total_salary'] += $userData['medical_allowance'] ?? $user->medical_allowance ?? 0;
                $userData['total_salary'] += $userData['house_allowance'] ?? $user->house_allowance ?? 0;
                $userData['total_salary'] += $userData['food_allowance'] ?? $user->food_allowance ?? 0;
                $userData['total_salary'] += $userData['travel_allowance'] ?? $user->travel_allowance ?? 0;
                $userData['total_salary'] += $userData['security'] ?? $user->security ?? 0;
                $userData['total_salary'] += $userData['bonus'] ?? $user->bonus ?? 0;
            }

            $user->update($userData);

            // Handle commission agent data
            if (isset($data['is_commission_agent']) && $data['is_commission_agent'] && isset($data['commission_agent'])) {
                $commissionData = $data['commission_agent'];
                
                // Debug: Log commission agent data
                Log::info('Commission agent data received', [
                    'user_id' => $user->id,
                    'commission_data' => $commissionData,
                    'has_sale_target' => !empty($commissionData['sale_target']),
                    'has_target_period' => !empty($commissionData['target_period']),
                    'has_commission_percentage' => !empty($commissionData['commission_percentage'])
                ]);
                
                // Only validate if at least one field is provided
                if (!empty($commissionData['sale_target']) || !empty($commissionData['target_period']) || !empty($commissionData['commission_percentage'])) {
                    $commissionValidator = Validator::make($commissionData, [
                        'sale_target' => 'required|numeric|min:0',
                        'target_period' => 'required|in:daily,weekly,monthly,quarterly,yearly',
                        'commission_percentage' => 'required|numeric|min:0|max:100',
                    ]);

                    if (!$commissionValidator->fails()) {
                        // Check if commission agent record exists
                        $commissionAgent = $user->commissionAgent;
                        
                        if ($commissionAgent) {
                            // Update existing commission agent record
                            $commissionAgent->update([
                                'sale_target' => $commissionData['sale_target'],
                                'target_period' => $commissionData['target_period'],
                                'commission_percentage' => $commissionData['commission_percentage'],
                            ]);
                        } else {
                            // Create new commission agent record
                            CommissionAgent::create([
                                'user_id' => $user->id,
                                'sale_target' => $commissionData['sale_target'],
                                'target_period' => $commissionData['target_period'],
                                'commission_percentage' => $commissionData['commission_percentage'],
                            ]);
                        }
                    } else {
                        // Log commission agent validation errors
                        Log::warning('Commission agent validation failed', [
                            'user_id' => $user->id,
                            'errors' => $commissionValidator->errors()->toArray(),
                            'data' => $commissionData
                        ]);
                    }
                }
            } elseif (isset($data['is_commission_agent']) && !$data['is_commission_agent']) {
                // If user is no longer a commission agent, delete the commission agent record
                if ($user->commissionAgent) {
                    $user->commissionAgent->delete();
                }
            }

        return response()->json([
                'success' => true,
            'message' => 'User updated successfully',
                'data' => $user->load(['userRole', 'businessLocation', 'commissionAgent'])
            ]);

        } catch (\Exception $e) {
            Log::error('User update failed', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
        $user->delete();

        return response()->json([
                'success' => true,
            'message' => 'User deleted successfully'
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users for commission agent selection
     */
    public function getUsersForCommission()
    {
        try {
            $users = User::where('is_commission_agent', false)
                ->select('id', 'name', 'first_name', 'last_name', 'email', 'phone')
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users for commission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 