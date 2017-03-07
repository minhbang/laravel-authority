<?php
Route::group(
    ['prefix' => 'backend', 'namespace' => 'Minhbang\Authority\Controllers'],
    function () {
        // Role Manage
        Route::group(
            ['prefix' => 'role', 'as' => 'backend.role.', 'middleware' => config('authority.middlewares.role')],
            function () {
                Route::get('/', ['as' => 'index', 'uses' => 'RoleController@index']);
                Route::get('{role}', ['as' => 'show', 'uses' => 'RoleController@show']);
                // Link User
                Route::group(
                    ['prefix' => '{role}/user', 'as' => 'user.'],
                    function () {
                        Route::post('{user}', ['as' => 'attach', 'uses' => 'RoleController@attachUser']);
                        Route::delete('{user}', ['as' => 'detach', 'uses' => 'RoleController@detachUser']);
                        Route::delete('/', ['as' => 'detach_all', 'uses' => 'RoleController@detachAllUser']);
                    }
                );
            }
        );
    }
);
