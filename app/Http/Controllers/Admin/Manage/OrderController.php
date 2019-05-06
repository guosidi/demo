<?php
/**
 * Created by PhpStorm.
 * User: guosidi
 * Date: 2018/9/18
 * Time: 16:27
 */

namespace App\Http\Controllers\Admin\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OpenArea;
use App\Models\Order;
use App\Models\OrdersGoods;
use App\Models\OrdersShipping;
use App\Tools\Admin\Response;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use App\Jobs\AutoReceivedOrderJob;

class OrderController extends Controller
{
    private $orderStatus = [
        1 => '待发货',
        2 => '待收货',
        3 => '已收货'
    ];
    private $payType = [
        1 => '微信',
        2 => '支付宝',
        0 => '未知'
    ];

    /**
     * 首页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $auth_province_id = self::$auth_province_id;
        if ($request->ajax()) {
            $draw = $request->input("draw");
            $start = $request->input("start");
            $length = $request->input("length");
            $order = $request->input('order');

            $columns = [
                'order_id', 'canteen_id', 'consignee', 'address', 'mobile', 'pay_fee', 'order_status', 'created_at', 'payed_at'
            ];

            $db = Order::leftJoin('recmall.canteen', 'orders.canteen_id', '=', 'recmall.canteen.canteen_id')
                ->leftJoin('recmall.canteen_owner', 'recmall.canteen_owner.canteen_id', '=', 'recmall.canteen.canteen_id')
                ->select('orders.order_id', 'orders.canteen_name', 'orders.consignee',
                    'orders.province_name', 'orders.city_name', 'orders.zone_name',
                    'orders.address', 'orders.zip', 'orders.mobile',
                    'order_status', 'pay_fee', 'orders.created_at',
                    'orders.payed_at', 'canteen_owner.phone')
                ->where('orders.is_pay', 1);

            $order_count = Order::where('orders.is_pay', 1);

            if ($keywords = $request->input('keywords')) {
                $db = $db->where(function ($query) use ($keywords) {
                    $query->orWhere('orders.order_id', $keywords)
                        ->orWhere('orders.canteen_name', 'like', '%' . $keywords . '%')
                        ->orWhere('orders.mobile', $keywords);
                });

                $order_count = $order_count->where(function ($query) use ($keywords) {
                    $query->orWhere('orders.order_id', $keywords)
                        ->orWhere('orders.canteen_name', 'like', '%' . $keywords . '%')
                        ->orWhere('orders.mobile', $keywords);
                });
            }
            if ($auth_province_id) {
                $db = $db->where('orders.province_id', $auth_province_id);
                $order_count = $order_count->where('orders.province_id', $auth_province_id);
            } else {
                if ($province_id = $request->input('province_id')) {
                    $db = $db->where('orders.province_id', $province_id);
                    $order_count = $order_count->where('orders.province_id', $province_id);
                }
            }

            if ($city_id = $request->input('city_id')) {
                $db = $db->where('orders.city_id', $city_id);
                $order_count = $order_count->where('orders.city_id', $city_id);
            }
            if ($start_time = $request->input('start_time')) {
                $db = $db->where('orders.created_at', '>=', $start_time);
                $order_count = $order_count->where('orders.created_at', '>=', $start_time);
            }
            if ($end_time = $request->input('end_time')) {
                $db = $db->where('orders.created_at', '<=', $end_time . ' 23:59:59');
                $order_count = $order_count->where('orders.created_at', '<=', $end_time . ' 23:59:59');
            }
            if ($status = $request->input('status')) {
                $db = $db->where('orders.order_status', $status);
            }

            $return['recordsTotal'] = $db->count();
            $return['draw'] = $draw;
            $return['recordsFiltered'] = $db->count();
            $return["data"] = [];

            $count = $order_count->where('order_status', 1)->count();
            $return['order_count'] = $count;

            $orderList = $db->skip($start)->take($length)->orderBy($columns[$order[0]['column']], $order[0]['dir'])->get();

            foreach ($orderList as $key => $value) {
                $order_id = $value->order_id;
                $button = "";
                if ($value->order_status == 1) {
                    $button .= '<button type="button" class="btn green btn-xs btn-detail" data-id="' . $order_id . '"><i class="fa fa-edit"></i> 操作发货</button>';
                } else {
                    $button .= '<button type="button" class="btn green btn-xs btn-detail" data-id="' . $order_id . '"><i class="fa fa-edit"></i> 明细</button>';
                }
                $button .= '<button type="button" class="btn green btn-xs btn-remark" data-id="' . $order_id . '"><i class="fa fa-edit"></i> 备注</button>';
                if ($value->order_status == '') {
                    $value->order_status = '';
                } else {
                    $value->order_status = $this->orderStatus[$value->order_status];
                }
                $value->payed_at = date('Y-m-d H:i:s', $value->payed_at);
                if ($value->zip == 0) {
                    $value->zip = '';
                }
                $return["data"][] = [
                    $order_id,
                    $value->phone . '<br/>' . $value->canteen_name,
                    $value->consignee,
                    $value->province_name . $value->city_name . $value->zone_name . $value->address . ',<br/>邮编:' . $value->zip,
                    $value->mobile,
                    '&yen;' . $value->pay_fee,
                    $value->order_status,
                    $value->created_at->format('Y-m-d H:i:s'),
                    $value->payed_at,
                    $button,
                ];
            }
            return response()->json($return);
        }
        $openProvinceList = OpenArea::instance()->getOpenProvince($auth_province_id);
        $orderStatus = $this->orderStatus;


        return view('admin.manage.order.index', compact('openProvinceList', 'orderStatus', 'auth_province_id'));
    }


    public function detail($order_id)
    {
        $orderInfo = Order::instance()
            ->leftJoin('orders_shipping', 'orders.order_id', 'orders_shipping.order_id')
            ->where('orders.order_id', $order_id)
            ->select('orders.order_id', 'orders.canteen_name', 'orders.order_status', 'orders.created_at', 'orders.payed_at', 'orders.pay_fee', 'orders.business_remark', 'orders.payment_id', 'orders.consignee', 'orders.mobile', 'orders.province_name', 'orders.city_name', 'orders.zone_name', 'orders.address', 'orders.zip', 'orders.business_remark', 'orders.remark', 'orders.pay_code', 'orders_shipping.shipping_name', 'orders_shipping.shipping_code')
            ->first();
        $goods = [];
        if ($orderInfo) {
            $goodsList = OrdersGoods::instance()
                ->where('orders_goods.order_id', $order_id)
                ->select('orders_goods.lottery_name', 'orders_goods.goods_number', 'orders_goods.total_price', 'orders_goods.goods_price')
                ->get();
            $goods = $goodsList;
            if ($orderInfo->order_status == '') {
                $orderInfo->order_status = '';
            } else {
                $orderInfo->order_status = $this->orderStatus[$orderInfo->order_status];
            }
            $orderInfo->payed_at = date('Y-m-d H:i:s', $orderInfo->payed_at);
            $orderInfo->payment_id = $this->payType[$orderInfo->payment_id];
        }
        $orderInfo['goodsList'] = $goods;
        return view('admin.manage.order.detail', compact('orderInfo'));

    }

    public function handleOrder(Request $request)
    {
        $order_id = $request->input('order_id');
        $shipping_name = $request->input('shipping_name');
        $shipping_code = $request->input('shipping_code');
        DB::beginTransaction();
        try {
            $OrdersShipping = new OrdersShipping();
            $OrdersShipping->order_id = $order_id;
            $OrdersShipping->shipping_name = $shipping_name;
            $OrdersShipping->shipping_code = $shipping_code;
            if (!$OrdersShipping->save()) {
                throw new Exception('发货失败');
            }
            $info = Order::instance()->where('order_id', $order_id)
                ->update(['order_status' => 2, 'shipping_at' => time()]);
            if (!$info) {
                throw new Exception('订单状态修改失败');
            }
            DB::commit();
            AutoReceivedOrderJob::dispatch($order_id)->delay(now()->addHours(240));
            return Response::success('操作发货成功');
        } catch (Exception $exception) {
            DB::rollBack();
            return Response::error($exception->getMessage());
        }
    }

    public function remark(Request $request)
    {
        $order_id = $request->input('id');
        $info = Order::find($order_id);
        if (!$info) {
            return Response::error('信息不存在');
        }

        if ($request->isMethod('post')) {
            $business_remark = $request->input('business_remark');
            $info->business_remark = $business_remark;
            if ($info->save()) {
                return Response::success();
            } else {
                return Response::error();
            }
        }
        $render = view('admin.manage.order.remark', compact('info'))->render();
        return Response::success($render);

    }

    public function getOrderCount($order_status)
    {
        $auth_province_id = self::$auth_province_id;
        $db = Order::instance()->where('order_status', $order_status);
        if ($auth_province_id) {
            $db = $db->where('province_id', $auth_province_id);
        }
        return $db->count();

    }


}