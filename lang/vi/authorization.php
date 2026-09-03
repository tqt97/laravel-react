<?php

return [
    'denial' => [
        'insufficient_permission' => 'Bạn không có quyền thực hiện thao tác này.',
        'self_target' => 'Bạn không thể thay đổi phân quyền của chính mình.',
        'higher_privilege_target' => 'Đối tượng có quyền hiệu lực cao hơn bạn.',
        'system_role' => 'Không thể thay đổi system role.',
        'last_super_admin' => 'Phải luôn bảo vệ Super Admin cuối cùng.',
        'invalid_delegation' => 'Ủy quyền phân quyền này không hợp lệ.',
        'selected_users_missing' => 'Một hoặc nhiều người dùng đã chọn không còn tồn tại.',
        'selected_users_cannot_delete' => 'Một hoặc nhiều người dùng đã chọn không thể bị xóa.',
        'selected_users_cannot_restore' => 'Một hoặc nhiều người dùng đã chọn không thể khôi phục.',
        'last_admin' => 'Phải luôn bảo vệ tài khoản quản trị viên cuối cùng.',
    ],
    'roles' => ['super_admin' => 'Super Admin', 'admin' => 'Quản trị viên', 'user' => 'Người dùng'],
    'permissions' => [
        'dashboard_view' => ['label' => 'Xem dashboard', 'group' => 'Hệ thống'],
        'users_view' => ['label' => 'Xem người dùng', 'group' => 'Người dùng'],
        'users_manage' => ['label' => 'Quản lý người dùng', 'group' => 'Người dùng'],
        'roles_view' => ['label' => 'Xem vai trò', 'group' => 'Vai trò'],
        'roles_manage' => ['label' => 'Quản lý vai trò', 'group' => 'Vai trò'],
        'permissions_view' => ['label' => 'Xem permission', 'group' => 'Permission'],
        'permissions_manage' => ['label' => 'Đồng bộ permission', 'group' => 'Permission'],
        'audit_view' => ['label' => 'Xem audit log', 'group' => 'Audit'],
    ],
];
