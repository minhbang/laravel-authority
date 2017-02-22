<?php
namespace Minhbang\Authority;

use DB;

/**
 * Class RoleManager
 *
 * @package Minhbang\User
 */
class RoleManager
{
    /**
     * @var \Minhbang\User\Role[]
     */
    protected $roles = [];

    /**
     * Danh sách các nhóm roles
     *
     * @var array
     */
    protected $groups;
    /**
     * Đã đếm xố lượng users chưa
     *
     * @var bool
     */
    protected $counted = false;

    /**
     * RoleManager constructor.
     *
     * @param array $all
     * @param array $groups
     */
    public function __construct($all = [], $groups)
    {
        foreach ($all as $group => $roles) {
            $this->roles[$group] = [];
            foreach ($roles as $name => $level) {
                $this->roles[$group][$name] = new Role($group, $name, $level);
            }
        }

        $this->groups = (array)$groups;
    }

    /**
     * Lấy role: $params = 'sys.admin'
     * Lấy attribute của role: $params = 'sys.admin.level'
     *
     * @param null|string $params
     * @param mixed $default
     *
     * @return null|\Minhbang\User\Role
     */
    public function roles($params = null, $default = null)
    {
        if (empty($params)) {
            return $this->roles;
        }
        $params = explode('.', $params, 3);
        if (empty($params[1])) {
            return isset($this->roles[$params[0]]) ? $this->roles[$params[0]] : $default;
        } else {
            if (isset($this->roles[$params[0]][$params[1]])) {
                return $this->roles[$params[0]][$params[1]]->get(isset($params[2]) ? $params[2] : null, $default);
            } else {
                return $default;
            }
        }
    }

    /**
     * @param string $group
     *
     * @return string|array
     */
    public function getRoleByGroupName($group)
    {
        return is_string($group) && isset($this->groups[$group]) ? $this->groups[$group] : $group;
    }



    /**
     * All roles, bỏ các guarded roles
     *
     * @param null|string $params
     * @param mixed $default
     *
     * @return array|string
     */
    public function guarded($params = null, $default = null)
    {
        $roles = $this->roles;
        array_forget($roles, 'sys.sadmin');

        return array_get($roles, $params, $default);
    }
}