<?php
return [
    // Định nghĩa menus cho authority
    'menus' => [
        'backend.sidebar.user.role' => [
            'priority' => 3,
            'url'      => 'route:backend.role.index',
            'label'    => 'trans:user::role.roles',
            'icon'     => 'fa-male',
            'active'   => 'backend/role*',
        ],
    ],

    'middlewares' => [
        'role' => ['web', 'sys.sadmin'],
    ],
    /**
     * Định nghĩa các chức vụ
     * - Group: nhóm chức vụ, tên nhóm phải duy nhất
     * - Role: chức vụ, tên chức vụ duy nhất trong nhóm
     * - Trong một nhóm, Role có level cao hơn sẽ kế thừa các permission của role thấp hơn
     *
     * ==> Định danh một Role: 'group.role'
     */
    'roles'       => [
        // hệ thống
        'sys' => [
            'sadmin' => 200,
            'admin'  => 100,
        ],
    ],
    /**
     * Định nghĩa các nhóm chức vụ, nhóm 1 số roles để ghi ngắn gọn (có nghĩa)
     */
    'role_groups' => [
        'administrator' => ['sys.sadmin', 'sys.admin'],
    ],
];