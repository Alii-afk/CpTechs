<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserRole;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userRoles = UserRole::with(['users', 'tabPermissions.permissionTab', 'tabPermissions.permissionType'])->get();
        
        return response()->json([
            'data' => $userRoles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_roles',
            'status' => 'required|in:active,inactive'
        ]);

        $userRole = UserRole::create($request->all());

        return response()->json([
            'message' => 'User role created successfully',
            'data' => $userRole
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserRole $userRole)
    {
        return response()->json([
            'data' => $userRole->load(['users', 'tabPermissions.permissionTab', 'tabPermissions.permissionType'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserRole $userRole)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:user_roles,name,' . $userRole->id,
            'status' => 'sometimes|required|in:active,inactive'
        ]);

        $userRole->update($request->all());

        return response()->json([
            'message' => 'User role updated successfully',
            'data' => $userRole
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserRole $userRole)
    {
        $userRole->delete();

        return response()->json([
            'message' => 'User role deleted successfully'
        ]);
    }

    /**
     * Assign permissions to a user role
     */
    public function assignPermissions(Request $request, UserRole $userRole)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.tab_id' => 'required|exists:permission_tabs,id',
            'permissions.*.permission_type_id' => 'required|exists:permission_types,id'
        ]);

        // Clear existing permissions
        $userRole->tabPermissions()->detach();

        // Attach new permissions
        foreach ($request->permissions as $permission) {
            $tabPermission = \App\Models\TabPermission::where('tab_id', $permission['tab_id'])
                ->where('permission_type_id', $permission['permission_type_id'])
                ->first();

            if ($tabPermission) {
                $userRole->tabPermissions()->attach($tabPermission->id);
            }
        }

        return response()->json([
            'message' => 'Permissions assigned successfully',
            'data' => $userRole->load(['tabPermissions.permissionTab', 'tabPermissions.permissionType'])
        ]);
    }
} 