<?php
/**
 * Created by PhpStorm.
 * User: guosidi
 * Date: 2018/9/18
 * Time: 18:18
 */

namespace App\Http\Controllers\Admin\Canteen;

use App\Models\OpenArea;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $auth_province_id = self::$auth_province_id;
        if ($request->ajax()) {

            $draw = $request->input("draw");
            $start = $request->input("start");
            $length = $request->input("length");
            $order = $request->input('order');

            $columns = [
                'orders.canteen_id', 'orders.canteen_name', 'orders.address', 'sum_fee', 'order_count', 'created_at'
            ];

            $query = Order::leftJoin('recmall.canteen', 'recmall.canteen.canteen_id', '=', 'orders.canteen_id')
                ->leftJoin('recmall.canteen_owner', 'recmall.canteen_owner.canteen_id', '=', 'recmall.canteen.canteen_id')
                ->leftJoin('user_address', 'user_address.canteen_id', '=', 'orders.canteen_id')
                ->select('orders.canteen_id',
                    'recmall.canteen.canteen_name',
                    'recmall.canteen_owner.phone',
                    'user_address.province_id',
                    'user_address.province_name',
                    'user_address.city_id',
                    'user_address.city_name',
                    'user_address.district_id',
                    'user_address.zone_name',
                    'user_address.address',
                    'orders.zip')
                ->selectRaw('count(order_id) as order_count,sum(pay_fee) as sum_fee,MAX(orders.created_at) as created_at')
                ->where('order_status', 3)
                ->where('recmall.canteen_owner.is_owner', 1)
                ->where('recmall.canteen_owner.status', '<>', -1);

            if ($auth_province_id) {
                $query = $query->where('orders.province_id', $auth_province_id);
            } else {
                if ($province_id = $request->input('province_id')) {
                    $query = $query->where('orders.province_id', $province_id);
                }
            }
            if ($city_id = $request->input('city_id')) {
                $query = $query->where('orders.city_id', $city_id);
            }
            if ($start_time = $request->input('start_time')) {
                $query = $query->where('orders.created_at', '>=', $start_time);
            }
            if ($end_time = $request->input('end_time')) {
                $query = $query->where('orders.created_at', '<=', $end_time . ' 23:59:59');
            }
            if ($keyword = $request->input('keyword')) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orWhere('recmall.canteen.canteen_name', 'like', '%' . $keyword . '%')
                        ->orWhere('recmall.canteen_owner.phone', 'like', '%' . $keyword . '%');
                });
            }

            $query = $query->groupBy('orders.canteen_id');

            $money = DB::table(DB::raw("({$query->toSql()}) as sub"))
                ->mergeBindings($query->getQuery())
                ->sum('sum_fee');
            $return['money'] = $money;

            $count = DB::table(DB::raw("({$query->toSql()}) as sub"))
                ->mergeBindings($query->getQuery())
                ->count();

            $return['recordsTotal'] = $count;
            $return['draw'] = $draw;
            $return['recordsFiltered'] = $count;
            $return["data"] = [];

            $data = $query->skip($start)->take($length)->orderBy($columns[$order[0]['column']], $order[0]['dir'])->get();

            foreach ($data as $k => $v) {
                if ($v->city_name && $v->province_name) {
                    if (strstr($v->city_name, $v->province_name)) {
                        $address = $v->city_name . $v->zone_name . $v->address;
                    } else {
                        $address = $v->province_name . $v->city_name . $v->zone_name . $v->address;
                    }
                } else {
                    $address = $v->address;
                }

                $return["data"][] = [
                    $v->canteen_id,
                    $v->phone . '<br/>' . $v->canteen_name,
                    $address . '<br/>邮编:' . ($v->zip ?: ''),
                    '&yen;' . $v->sum_fee,
                    $v->order_count,
                    $v->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json($return);
        }

        $province = OpenArea::instance()->getOpenProvince($auth_province_id);
        return view('admin.canteen.order.index', compact('province', 'auth_province_id'));
    }
}