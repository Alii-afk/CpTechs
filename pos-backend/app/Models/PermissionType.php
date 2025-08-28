<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug'
    ];

    /**
     * Get the tab permissions for this permission type
     */
    public function tabPermissions()
    {
        return $this->hasMany(TabPermission::class, 'permission_type_id');
    }
} 