<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionTab extends Model
{
    use HasFactory;

    protected $fillable = [
        'tab_name',
        'tab_slug'
    ];

    /**
     * Get the tab permissions for this tab
     */
    public function tabPermissions()
    {
        return $this->hasMany(TabPermission::class, 'tab_id');
    }

    /**
     * Get the user roles that have access to this tab
     */
    public function userRoles()
    {
        return $this->belongsToMany(UserRole::class, 'user_roles_permissions', 'tab_permission_id', 'user_role_id')
            ->using(TabPermission::class);
    }
} 