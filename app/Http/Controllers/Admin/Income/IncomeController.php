<?php
/**
 * Created by PhpStorm.
 * User: gaodonghui
 * Date: 2018/9/20
 * Time: 17:48
 */

namespace App\Http\Controllers\Admin\Income;

use App\Models\Order;
use App\Models\OpenArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class IncomeController extends Controller
{

    //订单收入明细
    public function index(Request $request)
    {
        $auth_province_id = self::$auth_province_id;
        if ($request->ajax()) {
            $draw = $request->input("draw");
            $start = $request->input("start");
            $length = $request->input("length");
            $order = $request->input('order');
            $columns = [
                'order_id', 'orders.canteen_id', 'consignee', 'address', 'pay_fee', 'created_at', 'payed_at', 'payment_id', 'pay_code'
            ];

            $db = Order::leftJoin('recmall.canteen', 'orders.canteen_id', '=', 'recmall.canteen.canteen_id')
                ->leftJoin('recmall.canteen_owner', 'recmall.canteen_owner.canteen_id', '=', 'recmall.canteen.canteen_id')
                ->select(
                    'orders.order_id',
                    'orders.canteen_name',
                    'orders.consignee',
                    'orders.province_name',
                    'orders.city_name',
                    'orders.zone_name',
                    'orders.address',
                    'orders.zip',
                    'orders.pay_fee',
                    'orders.created_at',
                    'orders.payed_at',
                    'recmall.canteen_owner.phone',
                    'orders.payment_id',
                    'orders.pay_code')
                ->where('order_status', 3);

            if ($auth_province_id) {
                $db = $db->where('orders.province_id', $auth_province_id);
            } else {
                if ($province_id = $request->input('province_id')) {
                    $db = $db->where('orders.province_id', $province_id);
                }
            }
            if ($city_id = $request->input('city_id')) {
                $db = $db->where('orders.city_id', $city_id);
            }
            if ($start_time = $request->input('start_time')) {
                $db = $db->where('orders.created_at', '>=', $start_time);
            }
            if ($end_time = $request->input('end_time')) {
                $db = $db->where('orders.created_at', '<=', $end_time . ' 23:59:59');
            }
            if ($keyword = $request->input('keyword')) {
                $db = $db->where(function ($q) use ($keyword) {
                    $q->orWhere('orders.order_id', 'like', '%' . $keyword . '%')
                        ->orWhere('recmall.canteen.canteen_name', 'like', '%' . $keyword . '%')
                        ->orWhere('recmall.canteen.telephone', 'like', '%' . $keyword . '%');
                });
            }

            $money = DB::table(DB::raw("({$db->toSql()}) as sub"))
                ->mergeBindings($db->getQuery())
                ->sum('pay_fee');
            $return['money'] = $money;

            $return['recordsTotal'] = $db->count();
            $return['draw'] = $draw;
            $return['recordsFiltered'] = $db->count();
            $return["data"] = [];

            $orderList = $db->skip($start)->take($length)->orderBy($columns[$order[0]['column']], $order[0]['dir'])->get();

            foreach ($orderList as $k => $v) {
                if ($v->payment_id == 1) {
                    $v->payment_id = '微信';

                } else {
                    $v->payment_id = '支付宝';
                }
                $return["data"][] = [
                    $v->order_id,
                    $v->phone . '<br/>' . $v->canteen_name,
                    $v->consignee,
                    $v->province_name . $v->city_name . $v->zone_name . '<br/>' . $v->address . '<br/>邮编:' . ($v->zip ?: ''),
                    '&yen;' . $v->pay_fee,
                    $v->created_at->format('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s', $v->payed_at),
                    $v->payment_id,
                    $v->pay_code
                ];
            }
            return response()->json($return);
        }

        $openProvinceList = OpenArea::instance()->getOpenProvince($auth_province_id);
        return view('admin.income.income', compact('openProvinceList', 'auth_province_id'));
    }

}
