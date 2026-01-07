<?php

/**
 * Auth Helper Functions
 * Helper untuk check authentication dan permission
 */

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     */
    function is_logged_in(): bool
    {
        $session = \Config\Services::session();
        return $session->get('logged_in') ? true : false;
    }
}

if (!function_exists('user_id')) {
    /**
     * Get current user ID
     */
    function user_id(): ?int
    {
        $session = \Config\Services::session();
        return $session->get('user_id');
    }
}

if (!function_exists('user_role')) {
    /**
     * Get current user role name
     */
    function user_role(): ?string
    {
        $session = \Config\Services::session();
        return $session->get('role_name');
    }
}

if (!function_exists('user_role_level')) {
    /**
     * Get current user role level
     */
    function user_role_level(): int
    {
        $session = \Config\Services::session();
        return (int) $session->get('role_level') ?? 999;
    }
}

if (!function_exists('user_name')) {
    /**
     * Get current user name
     */
    function user_name(): string
    {
        $session = \Config\Services::session();
        return $session->get('employee_name') ?? $session->get('username') ?? 'User';
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if user has specific permission
     * 
     * @param string $permission Permission name (e.g., 'user.create')
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        $session = \Config\Services::session();
        $permissions = $session->get('permissions') ?? [];
        
        // Super Admin has all permissions
        if (user_role_level() === 1) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }
}

if (!function_exists('has_any_permission')) {
    /**
     * Check if user has any of the specified permissions
     * 
     * @param array $permissions Array of permission names
     * @return bool
     */
    function has_any_permission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (has_permission($permission)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('has_all_permissions')) {
    /**
     * Check if user has all specified permissions
     * 
     * @param array $permissions Array of permission names
     * @return bool
     */
    function has_all_permissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!has_permission($permission)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if user is super admin
     */
    function is_super_admin(): bool
    {
        return user_role_level() === 1;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if user is admin or super admin
     */
    function is_admin(): bool
    {
        return user_role_level() <= 2;
    }
}

if (!function_exists('can_access_module')) {
    /**
     * Check if user can access specific module
     * 
     * @param string $module Module name
     * @return bool
     */
    function can_access_module(string $module): bool
    {
        $session = \Config\Services::session();
        $permissions = $session->get('permissions') ?? [];
        
        // Super Admin has all access
        if (user_role_level() === 1) {
            return true;
        }
        
        // Check if user has any permission in this module
        foreach ($permissions as $permission) {
            if (strpos($permission, $module . '.') === 0) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('redirect_if_not_logged_in')) {
    /**
     * Redirect to login if not logged in
     */
    function redirect_if_not_logged_in()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu!');
        }
    }
}

if (!function_exists('user_info')) {
    /**
     * Get all user session info
     * 
     * @return array
     */
    function user_info(): array
    {
        $session = \Config\Services::session();
        return [
            'user_id' => $session->get('user_id'),
            'username' => $session->get('username'),
            'email' => $session->get('email'),
            'role_name' => $session->get('role_name'),
            'role_level' => $session->get('role_level'),
            'employee_id' => $session->get('employee_id'),
            'employee_name' => $session->get('employee_name'),
            'department_name' => $session->get('department_name'),
            'position_name' => $session->get('position_name'),
            'permissions' => $session->get('permissions') ?? []
        ];
    }
}

if (!function_exists('permission_button')) {
    /**
     * Generate button with permission check
     * 
     * @param string $permission Required permission
     * @param string $label Button label
     * @param string $url Button URL
     * @param string $class Button CSS class
     * @return string HTML button or empty string
     */
    function permission_button(string $permission, string $label, string $url, string $class = 'btn btn-primary'): string
    {
        if (!has_permission($permission)) {
            return '';
        }
        
        return '<a href="' . base_url($url) . '" class="' . $class . '">' . $label . '</a>';
    }
}

if (!function_exists('show_if_permission')) {
    /**
     * Show content only if user has permission
     * 
     * @param string $permission Required permission
     * @param string $content Content to show
     * @return string Content or empty string
     */
    function show_if_permission(string $permission, string $content): string
    {
        return has_permission($permission) ? $content : '';
    }
}