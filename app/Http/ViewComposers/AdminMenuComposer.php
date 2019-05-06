<?php

namespace App\Http\ViewComposers;

use App\Models\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AdminMenuComposer
{

    public function __construct()
    {

    }

    public function compose(View $view)
    {
        $admin_id = Auth::id();
        if ($admin_id == 1) {
            //超级管理员
            $auth = Admin\Permissions::pluck('name')->toArray();
        } else {
            $role_id = Admin\Role\User::where('admin_id', $admin_id)->value('role_id');
            $permission_id = Admin\Permissions\Role::where('role_id', $role_id)->pluck('permission_id')->toArray();

            $auth = Admin\Permissions::whereIn('id', $permission_id)->pluck('name')->toArray();
        }
        $view->with('adminMenus', $this->getMenu($auth));
    }

    /**
     * 获取左侧菜单
     * @param array $auth
     * @return array
     */
    function getMenu($auth = [])
    {
        $route = Route::currentRouteName();
        //一级菜单
        $list = Admin\Permissions::where("parent_id", 0)->orderBy('sort')->get();

        $menuList = [];

        foreach ($list as $k => $v) {

            //判断一级菜单是否在用户组权限内 如果有，则显示该一级菜单，否则不显示
            $auth_name = $v->name;
            if (!in_array($auth_name, $auth)) {
                continue;
            }

            $menuList[$k]['name'] = $v->display_name;
            $menuList[$k]['ico'] = $v->class_name ?? "icon-folder";
            //二级菜单
            $pid = $v->id;
            $sub = Admin\Permissions::where("parent_id", $pid)->orderBy('sort')->get();

            foreach ($sub as $key => $value) {
                //判断二级菜单是否在用户组权限中
                $children_auth_name = $value->name;
                if (!in_array($children_auth_name, $auth)) {
                    continue;
                }

                if (strstr($route, $children_auth_name)) {
                    $menuList[$k]['children'][$key]['select'] = true;
                    $menuList[$k]['select'] = true;
                }
                //链接地址
                $url = str_replace('_', '/', $children_auth_name);

                $menuList[$k]['children'][$key]['name'] = $value->display_name;
                $menuList[$k]['children'][$key]['url'] = $url;
            }
        }
        return $menuList;
    }
}