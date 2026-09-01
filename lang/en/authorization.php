<?php

return [
    'denial' => [
        'insufficient_permission' => 'You do not have permission to perform this action.',
        'self_target' => 'You cannot modify your own authorization.',
        'higher_privilege_target' => 'The target has higher effective privileges than you.',
        'system_role' => 'System roles cannot be modified.',
        'last_super_admin' => 'The last Super Admin must be preserved.',
        'invalid_delegation' => 'This authorization delegation is not allowed.',
        'selected_users_missing' => 'One or more selected users no longer exist.',
        'selected_users_cannot_delete' => 'One or more selected users cannot be deleted.',
        'selected_users_cannot_restore' => 'One or more selected users cannot be restored.',
        'last_admin' => 'The last administrator account must be preserved.',
    ],
    'roles' => ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'User'],
    'permissions' => [
        'dashboard_view' => ['label' => 'View dashboard', 'group' => 'System'],
        'users_view' => ['label' => 'View users', 'group' => 'Users'],
        'users_manage' => ['label' => 'Manage users', 'group' => 'Users'],
        'roles_view' => ['label' => 'View roles', 'group' => 'Roles'],
        'roles_manage' => ['label' => 'Manage roles', 'group' => 'Roles'],
        'permissions_view' => ['label' => 'View permissions', 'group' => 'Permissions'],
        'permissions_manage' => ['label' => 'Synchronize permissions', 'group' => 'Permissions'],
        'audit_view' => ['label' => 'View audit log', 'group' => 'Audit'],
    ],
];
