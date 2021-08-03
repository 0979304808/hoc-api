<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Laratrust\Laratrust;

class LaratrustController extends Controller
{
    public function index()
    {

    }
    // Tạo các chức năng cho Role
    public function create(Request $request)
    {
//        Laratrust::can('permission-name');

//        $role = Role::create();
//        $permission = Permission::create();
//        $role->attachPermission($permission);
    }
}
