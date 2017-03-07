<?php
namespace Minhbang\Authority;

use DB;

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
     * @var array
     */
    protected $roles;

    /**
     * @var array
     */
    protected $new_roles = [];

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
            return str_is($id, $role) || ($not_exact && authority()->role($id)->superiorOf($role));
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
        return DB::table('role_user')
            ->where('user_id', '=', $this->entity->id)
            ->select('role_group', 'role_name')->get()
            ->map(function ($role) {
                return "{$role->role_group}.{$role->role_name}";
            });
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
     * Setter $this->roles = $value
     *
     * @param array $value
     */
    /*public function setRolesAttribute($value)
    {
        $this->new_roles = (array)$value;
    }*/

    /*public function syncNewRoles()
    {
        if ($this->new_roles) {
            foreach ($this->new_roles as $role) {
                $this->attachRole($role);
            }
        }
    }*/

    /**
     * Role $attribute cao nhất từ danh sách $roles mà user được gán
     *
     * @param array $lists Danh sách role ID cần test
     * @param null $user_id
     * @param string $attribute
     *
     * @return null|string
     */
    /*public function topRole($lists = [], $user_id = null, $attribute = 'title')
    {
        $roles = $this->getUserRoles($user_id ?: user('id'));
        foreach ($lists as $role) {
            if (in_array($role, $roles)) {
                return $this->roles("{$role}.{$attribute}");
            }
        }

        return null;
    }*/

    /**
     * @param string $group
     * @param string $name
     *
     * @return bool
     */
    /*public function attachRole($group, $name = null)
    {
        if (is_null($name)) {
            list($group, $name) = explode('.', $group, 2);
        }
        $level = config("user.roles.{$group}.{$name}");
        if (!$level || !$this->exists) {
            return false;
        } else {
            if (DB::table('role_user')
                ->where('user_id', $this->id)
                ->where('role_group', $group)
                ->where('role_name', $name)
                ->count()
            ) {
                return true;
            } else {
                return DB::table('role_user')
                    ->insert(['user_id' => $this->id, 'role_group' => $group, 'role_name' => $name]);
            }
        }
    }*/
}