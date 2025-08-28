<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermissionTab;
use App\Models\PermissionType;
use App\Models\TabPermission;

class PermissionController extends Controller
{
    /**
     * Display a listing of all permission tabs
     */
    public function tabs()
    {
        $tabs = PermissionTab::with('tabPermissions.permissionType')->get();
        
        return response()->json([
            'data' => $tabs
        ]);
    }

    /**
     * Display a listing of all permission types
     */
    public function types()
    {
        $types = PermissionType::with('tabPermissions.permissionTab')->get();
        
        return response()->json([
            'data' => $types
        ]);
    }

    /**
     * Store a new permission tab
     */
    public function storeTab(Request $request)
    {
        $request->validate([
            'tab_name' => 'required|string|max:255',
            'tab_slug' => 'required|string|max:255|unique:permission_tabs'
        ]);

        $tab = PermissionTab::create($request->all());

        return response()->json([
            'message' => 'Permission tab created successfully',
            'data' => $tab
        ], 201);
    }

    /**
     * Store a new permission type
     */
    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permission_types'
        ]);

        $type = PermissionType::create($request->all());

        return response()->json([
            'message' => 'Permission type created successfully',
            'data' => $type
        ], 201);
    }

    /**
     * Create a tab permission (link tab with permission type)
     */
    public function storeTabPermission(Request $request)
    {
        $request->validate([
            'tab_id' => 'required|exists:permission_tabs,id',
            'permission_type_id' => 'required|exists:permission_types,id'
        ]);

        // Check if this combination already exists
        $existing = TabPermission::where('tab_id', $request->tab_id)
            ->where('permission_type_id', $request->permission_type_id)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'This tab permission already exists'
            ], 422);
        }

        $tabPermission = TabPermission::create($request->all());

        return response()->json([
            'message' => 'Tab permission created successfully',
            'data' => $tabPermission->load(['permissionTab', 'permissionType'])
        ], 201);
    }

    /**
     * Get all tab permissions
     */
    public function tabPermissions()
    {
        $tabPermissions = TabPermission::with(['permissionTab', 'permissionType'])->get();
        
        return response()->json([
            'data' => $tabPermissions
        ]);
    }
} 