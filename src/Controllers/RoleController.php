<?php
namespace Minhbang\Authority\Controllers;

use Minhbang\Authority\BackendManager;
use Minhbang\Authority\Role;
use Minhbang\Kit\Extensions\BackendController;
use Minhbang\User\User;

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
            trans('authority::common.manage'),
            'fa-male',
            ['#' => trans('authority::common.roles')]
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
            [trans('authority::role.manage') . ':', $role->full_title],
            'fa-male',
            [route('backend.role.index') => trans('authority::role.roles'), '#' => $role->full_title]
        );
        // Tất cả users đã được gán role này
        $users = $role->users();
        // 10 users khác chưa gán $role
        $selectize_users = User::forSelectize($users->pluck('id'), 10)->get()->all();

        return view('authority::role.show', compact('role', 'users', 'selectize_users'));
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
                'content' => trans('authority::role.attach_user_success'),
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
                'content' => trans('authority::role.detach_user_success'),
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
                'content' => trans('authority::role.detach_all_user_success'),
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
        abort_unless(authority()->definedRole($id), 404, trans('authority::role.invalid'));

        return authority()->role($id);
    }
}
