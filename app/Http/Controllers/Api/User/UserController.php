<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/20 0020
 * Time: 10:40
 */
namespace App\Http\Controllers\Api\User;
// 指定允许其他域名访问
//header('Access-Control-Allow-Origin:*');
// 响应类型
//header('Access-Control-Allow-Methods:*');
// 响应头设置
//header('Access-Control-Allow-Headers:x-requested-with,content-type');


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Api\User;
use App\Models\Area;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加或修改收货地址
     */
    public function add(Request $request){
        $userAddress = [];
        //店铺ID
        $userAddress['canteen_id'] = $request->input('canteen_id');
        //收货人姓名
        $userAddress['consignee'] = $request->input('consignee');
        //收货地址(省份)
        $userAddress['province_id'] = $request->input('province_id');
        //收货地址(城市)
        $userAddress['city_id'] = $request->input('city_id');
        //收货地址(区域)
        $userAddress['district_id'] = $request->input('district_id');
        //详细地址
        $userAddress['address'] = $request->input('address');
        //收货人电话
        $userAddress['mobile'] = $request->input('mobile');

        $data = User::instance()->add_address($userAddress);

        if ($data){
            return response()->json(array('data' => $data, 'code' => 200, 'message' => '添加成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 300, 'message' => '添加失败'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 查询当前店铺位置所在省下的所有市和区
     */
    public function selectArea(Request $request){
        //店铺ID
        $canteen_id = $request->input('canteen_id');
        $area = Area::instance()->selectCanteenaddress($canteen_id);
        if ($area){
            return response()->json(array('data' => $area, 'code' => 200, 'message' => '查询成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 309, 'message' => '没有查到'));
        }
    }
}