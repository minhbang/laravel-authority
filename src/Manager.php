<?php
namespace Minhbang\Authority;

/**
 * Class Manager
 *
 * @package Minhbang\Authority
 */
class Manager
{
    /**
     * Cache danh sách Role object, ['name' => Role]
     *
     * @var \Minhbang\Authority\Role[]
     */
    protected $roles = [];

    /**
     * @var array
     */
    protected $role_groups = [];

    /**
     * @var \Minhbang\Authority\User
     */
    protected $user;

    /**
     * Đã đếm số user cho từ role
     *
     * @var bool
     */
    protected $userCounted = false;

    public function __construct()
    {
        $this->role_groups = config('authority.role_groups');
    }


    /**
     * Authority của user hiện tại
     * Sử dụng: Authority::user()->is('sys.admin')
     *
     * @return User
     */
    public function user()
    {
        if ($this->user) {
            $this->user = new User();
        }

        return $this->user;
    }

    /**
     * Quản lý role $id
     * Sử dụng: Authority::role(sys.sadmin)->users()
     *
     * @param string $id
     *
     * @return Role
     */
    public function role($id)
    {
        if (empty($this->roles[$id])) {
            $level = config("authority.roles.$id");
            abort_unless($level && $this->validate($id), 500, "Error: Undefined Role $id !");

            $this->roles[$id] = new Role($id, $level);
        }

        return $this->roles[$id];
    }

    /**
     * @param $id
     *
     * @return boolean
     */
    public function definedRole($id)
    {
        return config("authority.roles.$id");
    }

    /**
     * Kiểm tra role ID, chắc chắn có dạng: group.name
     *
     * @param string $role
     *
     * @return bool
     */
    public function validate($role)
    {
        return preg_match('/^[a-z0-9_]+\.[a-z0-9_]+$/', $role);
    }

    /**
     * Lấy danh sách roles từ $argument, phân các role bằng ',' hoặc '|'
     * Sử dụng:
     * - parserRoles(['sys.admin', 'sys.sadmin'])
     * - parserRoles('sys.admin|sys.sadmin')
     * - parserRoles('administrator,sys.tester') ==> administrator: role group ==> chuyển đổi thành các role của nó
     *
     * @param string|array $argument
     * @param string $delimiter
     *
     * @return array
     */
    public function parserRoles($argument, $delimiter = ',|')
    {
        $roles = is_array($argument) ? $argument : preg_split("/ ?[$delimiter] ?/", $argument);
        $result = [];
        foreach ($roles as $role) {
            if (isset($this->role_groups[$role])) {
                // role group
                $result = array_merge($result, $this->role_groups[$role]);
            } else {
                // role bình thường
                $result[] = $role;
            }
        }

        return $result;
    }
}