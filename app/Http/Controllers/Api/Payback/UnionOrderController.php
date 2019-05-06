<?php
/**
 * Created by PhpStorm.
 * User: YanLong-Rui
 * Date: 2018/5/08
 * Time: 11:45
 * 通联收银宝商户接入 扫码支付功能类 主扫类型
 */
namespace App\Http\Controllers\Payback;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tools\Blogger;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Orders_operate_logModel;
use App\Models\Payment_logModel;
use App\Tools\PaymentFactory;
use App\Models\CanteenOwnerModel;
use App\Models\User;
use App\Models\payment_recordModel;
use App\Events\OrderRefunded;
use App\Tools\QueueEntrance;
class UnionOrderController extends Controller
{
    /**
    */
    public function unionorderPay(Request $request){
        $unionOrderObj =  PaymentFactory ::factory('UnionorderActive');

    }
}