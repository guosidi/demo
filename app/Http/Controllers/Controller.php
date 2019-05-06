<?php

namespace App\Http\Controllers;

use App\Models\Admin\Permissions;
use App\Models\Admin\Role\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static $access;
    protected static $auth_province_id = 0;
    protected static $auth_role;

    public function __construct()
    {
        if (Auth::check()) {
            $admin_id = Auth::id();
            if ($admin_id == 1) {
                //固定超级管理员
                $auth = Permissions::pluck('name')->toArray();
                self::$auth_role = [1];
            } else {
                //其他登录人需要判断权限
                $role_id = User::where('admin_id', $admin_id)->pluck('role_id')->toArray();
                self::$auth_role = $role_id;

                $permission_id = Permissions\Role::whereIn('role_id', $role_id)->pluck('permission_id')->toArray();

                $auth = Permissions::whereIn('id', $permission_id)->pluck('name')->toArray();
            }

            self::$access = $auth;  //当前登录用户的权限

            self::$auth_province_id = Auth::user()->province_id;    //当前登录用户所管辖的区域
        }
    }
}
