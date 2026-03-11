<?php

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        // Super admin selalu lolos
        if (session()->get('role_name') === 'super_admin') {
            return true;
        }

        return in_array($permission, session()->get('permissions') ?? [], true);
    }
}