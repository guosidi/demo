<?php
/**
 * Created by PhpStorm.
 * User: luanjun
 * Date: 2017/4/19
 * Time: 16:46
 */

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Tools\Admin\Response;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * 权限检查中间件
 * Class AuthenticateAdmin
 * @package App\Http\Middleware
 */
class CheckAuthorityByAdmin
{

    /**
     * 权限控制
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        $route_name = Route::currentRouteName();

        $admin_id = Auth::id();
        if ($admin_id == 1) {
            //超级管理员
            $auth = Admin\Permissions::pluck('name')->toArray();
            view()->share('admin_access', $auth);

            return $next($request);
        } else {
            //其他登录人需要判断权限
            $role_id = Admin\Role\User::where('admin_id', $admin_id)->value('role_id');
            $permission_id = Admin\Permissions\Role::where('role_id', $role_id)->pluck('permission_id')->toArray();

            $auth = Admin\Permissions::whereIn('id', $permission_id)->pluck('name')->toArray();
            view()->share('admin_access', $auth);

            if ($route_name) {
                if (in_array($route_name, $auth)) {
                    return $next($request);
                } else {
                    if ($request->ajax()) {
                        return Response::alert('您没有权限执行此操作~', 403);
                    } else {
                        return response()->view('errors.403', [], 403);
                    }
                }
            } else {
                //空地址为首页，不需要检测权限
                return $next($request);
            }
        }

    }


}