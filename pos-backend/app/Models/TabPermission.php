<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'tab_id',
        'permission_type_id'
    ];

    /**
     * Get the permission tab
     */
    public function permissionTab()
    {
        return $this->belongsTo(PermissionTab::class, 'tab_id');
    }

    /**
     * Get the permission type
     */
    public function permissionType()
    {
        return $this->belongsTo(PermissionType::class, 'permission_type_id');
    }

    /**
     * Get the user roles that have this permission
     */
    public function userRoles()
    {
        return $this->belongsToMany(UserRole::class, 'user_roles_permissions', 'tab_permission_id', 'user_role_id');
    }
} 