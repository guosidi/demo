<?php

namespace App\Http\Controllers\Admin\System;

use App\Models\Admin;
use App\Models\OpenArea;
use App\Tools\Admin\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class AccountController extends Controller
{
    function index(Request $request)
    {
        if ($request->ajax()) {
            $draw = $request->input("draw");
            $start = $request->input("start");
            $length = $request->input("length");
            $order = $request->input('order');

            $columns = [
                'id', 'province_id', 'real_name', 'phone', 'account', 'created_at'
            ];

            $list = Admin::leftJoin('recmall.area', 'recmall.area.id', '=', 'admin.province_id')
                ->leftJoin('admin_role_user', 'admin_role_user.admin_id', '=', 'admin.id');
            if ($keyword = $request->input("keyword")) {
                $list = $list->where(function ($query) use ($keyword) {
                    $query->orWhere('account', 'like', '%' . $keyword . '%')
                        ->orWhere('real_name', 'like', '%' . $keyword . '%')
                        ->orWhere('phone', 'like', '%' . $keyword . '%');
                });
            }

            $list = $list->where('admin_role_user.role_id', '<>', 1);//超级管理员不展示
            $list = $list->select('admin.*', 'recmall.area.areaname as province');

            $return['recordsTotal'] = $list->count();
            $return['draw'] = $draw;
            $return['recordsFiltered'] = $list->count();
            $return["data"] = [];

            $data = $list->skip($start)->take($length)->orderBy($columns[$order[0]['column']], $order[0]['dir'])->get();

            foreach ($data as $k => $v) {
                $id = $v->id;

                $button = "";
                if (in_array("system_account_reset", self::$access)) {
                    $button .= '<button type="button" class="btn green btn-xs btn-reset" data-id="' . $id . '"><i class="fa fa-edit"></i> 重置密码</button>';
                }
                if (in_array("system_account_delete", self::$access)) {
                    $button .= '<button type="button" class="btn btn-danger btn-xs btn-del" data-id="' . $id . '"><i class="fa fa-trash"></i> 删除</button>';
                }

                $return["data"][] = [
                    $id,
                    $v->province,
                    $v->real_name,
                    $v->phone,
                    $v->account,
                    $v->created_at->format('Y-m-d H:i:s'),
                    $button,
                ];
            }

            return response()->json($return);
        }
        return view('admin.system.account.index');
    }

    function add(Request $request)
    {
        $province = OpenArea::instance()->getOpenProvince();

        if ($request->isMethod('post')) {
            $province_id = $request->input('province_id');
            $real_name = $request->input('real_name');
            $phone = $request->input('phone');
            $account = $request->input('account');

            $account_count = Admin::where('account', $account)->count();
            if ($account_count) {
                return Response::error('账号已存在');
            }

            DB::beginTransaction();
            try {
                $admin = new Admin();
                $password = Admin::instance()->defaultPassword($phone);
                $admin->account = $account;
                $admin->password = Hash::make($password);
                $admin->phone = $phone;
                $admin->real_name = $real_name;
                $admin->province_id = $province_id;
                $admin->creator = Auth::id();
                if (!$admin->save()) {
                    throw new Exception('管理员添加失败');
                }

                $role_user = new Admin\Role\User();
                $role_user->role_id = 2;
                $role_user->admin_id = $admin->id;
                if (!$role_user->save()) {
                    throw new Exception('管理员角色添加失败');
                }

                DB::commit();
                return Response::success();
            } catch (Exception $exception) {
                DB::rollBack();
                return Response::error($exception->getMessage());
            }

        }

        $render = view('admin.system.account.add', compact('province'))->render();
        return Response::success($render);
    }

    function reset(Request $request)
    {
        $id = $request->input('id');

        $info = Admin::find($id);
        $password = Admin::instance()->defaultPassword($info->phone);
        $info->password = Hash::make($password);
        if ($info->save()) {
            return Response::success();
        } else {
            return Response::error();
        }
    }

    function delete(Request $request)
    {
        $id = $request->input('id');
        DB::beginTransaction();
        try {
            if (!Admin::destroy($id)) {
                throw new Exception('删除管理员失败');
            }

            $result = Admin\Role\User::where('admin_id', $id)->delete();
            if (!$result) {
                throw new Exception('删除管理员角色失败');
            }

            DB::commit();
            return Response::success();
        } catch (Exception $exception) {
            DB::rollBack();
            return Response::error($exception->getMessage());
        }
    }
}
