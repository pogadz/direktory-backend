<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system_role',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
    ];

    /**
     * Relationship: Roles belong to many Permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    /**
     * Relationship: Roles belong to many Profiles
     */
    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_role')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->permissions()->whereIn('name', $permissionNames)->exists();
    }

    /**
     * Check if role has all of the given permissions
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        $permissionCount = $this->permissions()
            ->whereIn('name', $permissionNames)
            ->count();

        return $permissionCount === count($permissionNames);
    }

    /**
     * Assign permissions to this role
     */
    public function givePermissions(array $permissionIds): void
    {
        $this->permissions()->syncWithoutDetaching($permissionIds);
    }

    /**
     * Remove permissions from this role
     */
    public function revokePermissions(array $permissionIds): void
    {
        $this->permissions()->detach($permissionIds);
    }

    /**
     * Sync permissions (replace all permissions with new set)
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}
