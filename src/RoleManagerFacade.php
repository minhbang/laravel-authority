<?php
namespace Minhbang\Authority;

use Illuminate\Support\Facades\Facade;

class RoleManagerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'role-manager';
    }
}