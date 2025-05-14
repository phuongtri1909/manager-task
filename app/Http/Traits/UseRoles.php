<?php

namespace App\Http\Traits;

trait UseRoles
{
    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    /**
     * Check if user is director
     *
     * @return bool
     */
    public function isDirector(): bool
    {
        return $this->role?->slug === 'director';
    }

    /**
     * Check if user is deputy director
     *
     * @return bool
     */
    public function isDeputyDirector(): bool
    {
        return $this->role?->slug === 'deputy-director';
    }

    /**
     * Check if user is department head
     *
     * @return bool
     */
    public function isDepartmentHead(): bool
    {
        return $this->role?->slug === 'department-head';
    }

    /**
     * Check if user is deputy department head
     *
     * @return bool
     */
    public function isDeputyDepartmentHead(): bool
    {
        return $this->role?->slug === 'deputy-department-head';
    }

    /**
     * Check if user is staff
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->role?->slug === 'staff';
    }
} 