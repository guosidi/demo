<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19 0019
 * Time: 16:11
 */
namespace App\Http\Controllers\Api\Order;
// 指定允许其他域名访问
//header('Access-Control-Allow-Origin:*');
// 响应类型
//header('Access-Control-Allow-Methods:*');
// 响应头设置
//header('Access-Control-Allow-Headers:x-requested-with,content-type');


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Models\LotteryTicket;
use DB;

class OrderController extends Controller
{
    const PAGE_SIZE = 10;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 查询订单列表
     */
    public function selectOrderList(Request $request){
        //订单状态
        $order_status = 0;
        if ($request->has('order_status')){
            $order_status = $request->input('order_status');
        }
        //店铺ID
        $canteen_id = $request->input('canteen_id');
        //当前页
//        $page = 0;
//        if ($request->has("page")) {
//            $page = $request->input('page');
//        }

        $orderList = Order::instance()->selectOrderList($order_status,$canteen_id);

        if ($orderList){
            //分页一次返回十条、、
//            if ($page) {
//                $current_page = $page;
//                $current_page = $current_page <= 0 ? 1 :$current_page;
//            } else {
//                $current_page = 1;
//            }
//
//            $item = array_slice($orderList, ($current_page-1)*self::PAGE_SIZE, self::PAGE_SIZE); //注释1
//            $total = count($orderList);
//            $paginator =new LengthAwarePaginator($item, $total, self::PAGE_SIZE, $current_page, [
//                'path' => Paginator::resolveCurrentPath(),  //注释2
//                'pageName' => 'page',
//            ]);
//
//            $data = $paginator->toArray()['data'];
            return response()->json(array('data' => $orderList, 'code' => 200, 'message' => '查询成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 309, 'message' => '没有查到'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 查询订单详情
     */
    public function OrderFirst(Request $request){
        //订单ID
        $order_id = $request->input('order_id');

        $orderFirst = Order::instance()->orderFirst($order_id);
        if ($orderFirst){
            return response()->json(array('data' => $orderFirst, 'code' => 200, 'message' => '查询成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 309, 'message' => '没有查到'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 点击去结算时检测商品状态
     */
    public function TestingGoods(Request $request){
        $goods = $request->input('goods');
        $Testing = Order::instance()->TestingGoods($goods);
        if ($Testing == 200 ){
            return response()->json(array('data' => [], 'code' => 200, 'message' => '商品正常'));
        }else{
            return response()->json(array('data' => [], 'code' => 305, 'message' => '商品信息已变更，请重新确认'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 提交购物车计算价格展示位置
     */
    public function settlementOrder(Request $request){
        $canteen_id = $request->input('canteen_id');
        $lottery = $request->input('lottery');
        $order = Order::instance()->settlementOrder($canteen_id,$lottery);
        if ($order){
            return response()->json(array('data' => $order, 'code' => 200, 'message' => '查询成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 300, 'message' => '查询失败'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 查询在售彩票列表
     */
    public function select_lottery(Request $request){
        //店铺ID
        $canteen_id = $request->input('canteen_id');
        //当前页
//        $page = 0;
//        if ($request->has("page")) {
//            $page = $request->input('page');
//        }
        $LotteryTicket = new LotteryTicket();
        $lotteryList = $LotteryTicket->selectLottery($canteen_id);
        if ($lotteryList){
            //分页一次返回十条、、
//            if ($page) {
//                $current_page = $page;
//                $current_page = $current_page <= 0 ? 1 :$current_page;
//            } else {
//                $current_page = 1;
//            }
//
//            $item = array_slice($lotteryList, ($current_page-1)*6, 6); //注释1
//            $total = count($lotteryList);
//            $paginator =new LengthAwarePaginator($item, $total, 6, $current_page, [
//                'path' => Paginator::resolveCurrentPath(),  //注释2
//                'pageName' => 'page',
//            ]);

//            $data = $paginator->toArray()['data'];
            return response()->json(array('data' => $lotteryList, 'code' => 200, 'message' => '查询成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 309, 'message' => '没有更多'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 提交订单
     */
    public function commitOrder(Request $request){
        $canteen_id = $request->input('canteen_id');
        $goods = $request->input('goods');
        $pay_fee = $request->input('pay_fee');
        $payment_id = $request->input('payment_id');
        $remark = $request->input('remark');
        $order = Order::instance()->commitOrder($canteen_id,$goods,$pay_fee,$payment_id,$remark);
        if ($order == 305 ){
            return response()->json(array('data' => [], 'code' => 305, 'message' => '商品信息已变更，请重新确认'));
        }else{
            if ($order){
                return response()->json(array('data' => $order, 'code' => 200, 'message' => '提交成功'));
            }else{
                return response()->json(array('data' => [], 'code' => 300, 'message' => '提交失败'));
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 订单确认收货
     */
    public function confirmation(Request $request){
        $order_id = $request->input('order_id');
        $is_collect = $request->input('is_collect');
        $data = Order::instance()->collectGoods($order_id,$is_collect);
        if ($data){
            return response()->json(array('data' => [], 'code' => 200, 'message' => '确认收货成功'));
        }else{
            return response()->json(array('data' => [], 'code' => 300, 'message' => '确认收货失败'));
        }
    }
    
    
    /**
     * 临时加个方法，判断城市是否显示增值服务，返回1或者0
     */
    public function getOpenCity(Request $request){
        
        $province_id= $request->input('province_id');
        echo  DB::table("lottery_tickets")->whereNull('deleted_at')->where(['province_id'=>$province_id,])->count();
        
    }
    
    /**
     * 临时加个方法，判断城市是否显示增值服务，返回1或者0
     */
    public function sendupdateremain(Request $request){
        
        Order::instance()->testaa($request->input('order_id'));
        
    }
    
}