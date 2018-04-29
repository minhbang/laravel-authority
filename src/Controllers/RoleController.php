<?php

namespace Minhbang\Authority\Controllers;

use Minhbang\Authority\BackendManager;
use Minhbang\Authority\Role;
use Minhbang\Kit\Extensions\BackendController;
use Minhbang\User\User;
use Authority;

/**
 * Class RoleController
 *
 * @package Minhbang\Authority
 */
class RoleController extends BackendController
{
    /**
     * @var \Minhbang\Authority\BackendManager;
     */
    protected $manager;

    public function __construct()
    {
        parent::__construct();
        $this->manager = new BackendManager();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->buildHeading(
            __('Manage Roles'),
            'fa-male',
            ['#' => __('Roles')]
        );

        return view('authority::role.index', [
            'roles'      => $this->manager->roles,
            'countUsers' => $this->manager->countUsers,
        ]);
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $role = $this->getRole($id);
        $this->buildHeading(
            [__('Roles') . ':', $role->full_title],
            'fa-male',
            [route('backend.role.index') => __('Roles'), '#' => $role->full_title]
        );
        // Tất cả users đã được gán role này
        $users = $role->users();
        // 10 users khác chưa gán $role
        $selectize_users = User::forSelectize($users->pluck('id'), 10)->get()->all();

        // Permissions
        $attached_permissions = Authority::permission()->attachedTo($role);
        $permissions = $attached_permissions->groupByModel();
        $selectize_permissions = Authority::permission()->except($attached_permissions->keys()->all())->groupByModel();

        return view('authority::role.show',
            compact('role', 'users', 'selectize_users', 'permissions', 'selectize_permissions'));
    }

    // User Manage ----------------------------------------------------

    /**
     * @param string $id
     * @param \Minhbang\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachUser($id, User $user)
    {
        $this->getRole($id)->attachUser($user->id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => __('Attach User success!'),
            ]
        );
    }

    /**
     * @param string $id
     * @param \Minhbang\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachUser($id, User $user)
    {
        $this->getRole($id)->detachUser($user->id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => __('Detach User success!'),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachAllUser($id)
    {
        $this->getRole($id)->detachUser();

        return response()->json(
            [
                'type'    => 'success',
                'content' => __('Detach all User success!'),
            ]
        );
    }

    // Permission Manage ----------------------------------------------------

    /**
     * @param string $id
     * @param string $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachPermission($id, $permission_id)
    {
        $this->getRole($id)->attachPermission($permission_id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => __('Attach Permission success!'),
            ]
        );
    }

    /**
     * @param string $id
     * @param string $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachPermission($id, $permission_id)
    {
        $this->getRole($id)->detachPermission($permission_id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => __('Detach Permission success!'),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachAllPermission($id)
    {
        $this->getRole($id)->detachPermission();

        return response()->json(
            [
                'type'    => 'success',
                'content' => __('Detach all Permission success!'),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return Role
     */
    protected function getRole($id)
    {
        abort_unless(authority()->definedRole($id), 404, __('Invalid role ID'));

        return authority()->role($id);
    }
}
