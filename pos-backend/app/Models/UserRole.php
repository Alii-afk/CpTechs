<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the users that belong to this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the tab permissions for this role
     */
    public function tabPermissions()
    {
        return $this->belongsToMany(TabPermission::class, 'user_roles_permissions');
    }

    /**
     * Check if this role has a specific permission
     */
    public function hasPermission($tabSlug, $permissionType)
    {
        return $this->tabPermissions()
            ->whereHas('permissionTab', function($query) use ($tabSlug) {
                $query->where('tab_slug', $tabSlug);
            })
            ->whereHas('permissionType', function($query) use ($permissionType) {
                $query->where('slug', $permissionType);
            })
            ->exists();
    }

    /**
     * Get all permissions for this role
     */
    public function getAllPermissions()
    {
        return $this->tabPermissions()
            ->with(['permissionTab', 'permissionType'])
            ->get()
            ->map(function($tabPermission) {
                return [
                    'tab' => $tabPermission->permissionTab->tab_slug,
                    'permission' => $tabPermission->permissionType->slug
                ];
            });
    }
} 