<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_category_id',
        'name',
        'avatar',
        'bio',
        'status',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Check if profile has a specific role by name
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if profile has any of the given roles by name
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if profile has all of the given roles by name
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $profileRoles = $this->roles->pluck('name')->toArray();
        foreach ($roleNames as $roleName) {
            if (!in_array($roleName, $profileRoles)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Relationship: Profile belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Profile belongs to many Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'profile_role')
            ->withTimestamps();
    }

    /**
     * Check if profile has a specific permission through roles
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    /**
     * Check if profile has any of the given permissions
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionNames) {
                $query->whereIn('name', $permissionNames);
            })
            ->exists();
    }

    /**
     * Check if profile has all of the given permissions
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions through roles
     */
    public function getAllPermissions()
    {
        return Permission::whereHas('roles', function ($query) {
            $query->whereIn('roles.id', $this->roles->pluck('id'));
        })->get();
    }

    /**
     * Assign roles to this profile
     */
    public function assignRoles(array $roleIds): void
    {
        $this->roles()->syncWithoutDetaching($roleIds);
    }

    /**
     * Remove roles from this profile
     */
    public function removeRoles(array $roleIds): void
    {
        $this->roles()->detach($roleIds);
    }

    /**
     * Sync roles (replace all roles with new set)
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class);
    }
}
