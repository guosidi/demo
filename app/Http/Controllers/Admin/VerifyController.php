<?php
/**
 * Created by PhpStorm.
 * User: 栾军
 * Date: 2017/11/15
 * Time: 15:12
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Tools\Admin\Response;
use Illuminate\Http\Request;

/**
 * Description of CommonController
 *
 * @author admin
 */
class VerifyController extends Controller
{
    function index(Request $request)
    {
        $type = $request->input("type");
        if ($type == "system-account") {
            //验证用户名是否重复
            return $this->verifyAdminAccount($request);
        } else {
            return Response::error();
        }
    }

    function verifyAdminAccount(Request $request)
    {
        $account = $request->input("data");

        $count = Admin::where('account', $account)->count();
        if ($count) {
            return Response::error();
        } else {
            return Response::success();
        }
    }

}