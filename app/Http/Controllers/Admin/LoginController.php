<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Tools\Admin\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    /**
     * 登录
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $account = $request->input("account");
            $password = $request->input("password");

            $result = Admin::where('account', $account)->first();
            if ($result) {
                //验证用户账号密码
                if (Auth::guard('admin')->attempt(['account' => $account, 'password' => $password])) {
                    // 认证通过...
                    return Response::success();
                } else {
                    return Response::error('请输入正确的密码！');
                }
            } else {
                //没有找到账户
                return Response::error('请输入正确的用户名！');
            }
        }
        return view('admin.login');
    }

    function profile(Request $request)
    {
        $id = Auth::id();

        $admin = Admin::find($id);
        if (!$admin) {
            return Response::error('管理员信息获取错误');
        }

        $role_id = Admin\Role\User::where('admin_id', $id)->pluck('role_id')->toArray();
        if (in_array(1, $role_id)) {
            $message = '超级管理员无法修改账户信息(╥╯^╰╥)<br/>请前往运营后台进行修改(　＾∀＾)';
            if ($request->ajax()) {
                return Response::error($message);
            } else {
                return back()->withErrors($message);
            }
        }

        if ($request->isMethod('post')) {
            $password = $request->input('new');
            $admin->password = Hash::make($password);
            if ($admin->save()) {
                return Response::success();
            } else {
                return Response::error();
            }
        }

        return view('admin.profile', compact('admin'));
    }

    /**
     * 验证密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function verify(Request $request)
    {
        $id = Auth::id();

        $password = $request->input('password');
        $admin = Admin::find($id);
        if (!$admin) {
            return Response::error();
        }

        if (!Hash::check($password, $admin->password)) {
            return Response::error();
        } else {
            return Response::success();
        }
    }

    /**
     * 注销登录
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->user()) {
            Auth::guard('admin')->logout();
        }
        return Redirect::to('login');
    }
}
