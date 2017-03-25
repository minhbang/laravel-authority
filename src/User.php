<?php

namespace Minhbang\Authority;

use DB;
use Authority;

/**
 * Class User
 * Quản lý Authority của một User
 *
 * @package Minhbang\Authority
 */
class User
{
    /**
     * @var \Minhbang\User\User
     */
    protected $entity;
    /**
     * Cached roles
     *
     * @var \Illuminate\Support\Collection
     */
    protected $roles;

    /**
     * User constructor.
     *
     * @param \Minhbang\User\User $user
     */
    public function __construct($user)
    {
        $this->entity = $user;
    }

    /**
     * User đã được gán $roles?
     * $roles string: có thể nhiều roles phân cách bằng dấu ',' hoặc '|'
     *
     * @param string|array $roles
     * @param bool $all   Được gán tất cả các $roles hay chỉ cần 1
     * @param bool $exact Chính xác hay kế thừa
     *
     * @return bool
     */
    public function is($roles, $all = false, $exact = false)
    {
        return $all ? $this->isAll($roles, $exact) : $this->isOne($roles, $exact);
    }

    /**
     * Được gán ít nhất 1 role
     *
     * @param string|array $roles
     * @param bool $exact Chính xác hay kế thừa
     *
     * @return bool
     */
    public function isOne($roles, $exact = false)
    {
        foreach (authority()->parserRoles($roles) as $roles) {
            if ($this->hasRole($roles, $exact)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Được gán tất cả các role
     *
     * @param string|array $roles
     * @param bool $exact Chính xác hay kế thừa
     *
     * @return bool
     */
    public function isAll($roles, $exact = false)
    {
        foreach (authority()->parserRoles($roles) as $roles) {
            if (!$this->hasRole($roles, $exact)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kiểm tra $user có role $id.
     * - Có thể sử dụng * (xem https://laravel.com/docs/5.4/helpers#method-str-is), vd: 'bgh.*' tất cả role thuộc group Ban giám hiệu
     * - Mặc định kiểm tra KHÔNG 'chính xác'(kế thường), được gán role có level cao hơn sẽ có các role level thấp cùng group
     * ==> Kiểm tra không chính xác chỉ thực hiện khi role $id có dạng 'group.name', BỎ QUA khi dùng *, vd: 'group.*'
     *
     * @param string $id
     * @param bool $exact
     *
     * @return bool
     */
    public function hasRole($id, $exact = false)
    {
        if (empty($id)) {
            return false;
        }

        // super admin 'toàn quyền'
        if ($this->isSuperAdmin() && !$exact) {
            return true;
        }

        // kiểm tra 'không chính xác' khi: ($exact == false) và ($role có dạng 'group.name')
        $not_exact = !$exact && authority()->validate($id);
        // được gán TRỰC TIẾP || được gán role cùng group nhưng level cao hơn $role
        $this->roles()->contains(function ($role) use ($id, $not_exact) {
            return str_is($id, $role) || ($not_exact && authority()->role($id)->isSuperiorOf($role));
        });

        return false;
    }

    /**
     * Lấy danh sách ID roles User đã được gán
     *
     * @return \Illuminate\Support\Collection
     */
    public function roles()
    {
        if (is_null($this->roles)) {
            $this->roles = DB::table('role_user')
                ->where('user_id', '=', $this->entity->id)
                ->select('role_group', 'role_name')
                ->get()
                ->map(function ($role) {
                    return "{$role->role_group}.{$role->role_name}";
                });
        }

        return $this->roles;
    }

    /**
     * @return string[]
     */
    public function roleTitles()
    {
        return $this->roles()->map(function ($role) {
            return trans("authority::role.$role");
        })->toArray();
    }

    /**
     * Là super admin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->entity->username === 'admin';
    }

    /**
     * Thuộc nhóm Administrator: là Super Admin hoặc được gán role 'admin'
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->isSuperAdmin() || $this->isOne('sys.admin');
    }

    /**
     * Có được được phép thự hiện permission
     *
     * @param string $permission_id
     *
     * @return bool
     */
    public function can($permission_id)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        foreach ($this->roles() as $id) {
            if (Authority::role($id)->permissions()->contains($permission_id)) {
                return true;
            }
        }

        return false;
    }
}