<?php
/**
 * Created by PhpStorm.
 * User: ryl
 * Date: 2017/7/24
 * Time: 19:24
 */
namespace App\Http\Controllers\Api\Payback;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tools\PaymentFactory;
use App\Models\Payment_logModel;
use App\Models\payment_recordModel;
use App\Tools\Blogger;
use App\Models\Order;
use DB;
class WeChatController extends Controller
{
    /** @Author: YanLong-Rui
     *  @Modifications:微信同意下单接口
     *  @date:2017/7/24
     * @param Request $request
     * @param $out_trade_no int
     * @param $pay_fee double
     * @return void
     */
    public function weChatpPay(Request $request)
    {
        //接收参数
        //$dataAll = $request->all();
        $dataAll = ['out_trade_no'=>$request->input('out_trade_no'),'pay_fee'=>$request->input('pay_fee'),'clientIp'=>'1.1.1.1'];
        Blogger::__callStatic("notice", ['用户微信支付 统一下单 获取用户输入1.', [$dataAll], 'logs/wechat', date('Y-m-d')]);
        //处理逻辑
        $wechatobj = PaymentFactory::factory('Wechat');
        $dataforWechat = $wechatobj->getWechatData($dataAll);
        Blogger::__callStatic("notice", ['微信统一下单返回数据成功2.', [$dataforWechat], 'logs/wechat', date('Y-m-d')]);
        //返回数据
        if ($dataforWechat['return_code'] == "SUCCESS") {
            $dataArr = $wechatobj->getclientDataByPrepayId($dataforWechat['prepay_id']);
            //微信支付第一步返回客户端数据 添加payment_record表
            $orderInfo = Order::getOrderByIdFirst($request->input('out_trade_no'));
            //$userData = User::instance()->getUserById($orderInfo['user_id']);
            $recordData = [];
            $recordData['order_id'] = $request->input('out_trade_no');
            $recordData['user_id'] = $orderInfo->canteen_id;
            $recordData['user_name'] = $orderInfo->canteen_name;
            $recordData['trans'] = '';//交易号现在为空
            $recordData['pay_type'] = 1;//支付类型0扣扣，1支付宝，2微信
            $recordData['pay_code'] = serialize($dataArr);//此处是返回给客户端的数据
            $recordData['pay_fee'] = $request->input('pay_fee');//金额
            $recordData['status'] = 0;//0：去支付 1成功，2失败
            $recordData['create_time'] = time();
            $recordData['update_time'] = time();
            $recordData['pay_content'] = 'step1.微信支付返回客户端数据';
            payment_recordModel::instance()->insert($recordData);

            //修改支付方式
            Order::instance()->where('order_id',$orderInfo['order_id'])->update(['payment_id'=>'1','updated_at'=>time()]);
            
            return response()->json(array('data' => $dataArr, 'message' => '微信支付参数返回成功！', 'code' => 200));
        } else {
            // 错误日志便于追踪
            Blogger::__callStatic("notice", ['统一下单失败4.', [$dataforWechat], 'logs/wechat', date('Y-m-d')]);
            return response()->json(array('data' => $dataforWechat, 'message' => $dataforWechat['return_msg'], 'code' => 300));
        }
    }
    /** @Author: YanLong-Rui
     *  @Modifications:微信支付0元的时候调取接口
     *  @date:2017/7/24
     * @param Request $request
     * @param $out_trade_no int
     * @param $pay_fee double
     * @return Void
     */
    public function payfornullpayfee(Request $request)
    {
        if ($request->input('pay_fee') == 0) {
            $orderInfo = Order::instance()->getOrderById($request->input("out_trade_no"));
            //获取用户信息
            $userData = User::instance()->getUserById($orderInfo['user_id']);
            //处理逻辑
            $orderData['order_status'] = 20;
            $orderData['payed_at'] = time();
            $orderData['payment_id'] = 2;
            $orderData['is_pay'] = 1;//已付款
            $orderData['pay_type'] = 0;
            $orderData['updated_at'] = time();
            $result1 = Order::instance()->where("order_id", $request->input("out_trade_no"))->update($orderData);
            $dataOrderLog = [];
            $dataOrderLog['order_id'] = $request->input("out_trade_no");
            $dataOrderLog['type'] = 1;//1支付 2退款
            $dataOrderLog['pay_type'] = 2;//支付渠道 1支付宝 2微信
            $dataOrderLog['data'] = serialize($orderInfo);
            $dataOrderLog['created_at'] = time();
            $result2 = Orders_operate_logModel::getInstance()->insert($dataOrderLog);//执行添加订单日志表
            $datapaylog = [];
            $datapaylog['order_id'] = $request->input("out_trade_no");
            $datapaylog['pay_code'] = serialize($orderInfo);
            $datapaylog['create_time'] = time();
            $datapaylog['update_time'] = time();
            $result3 = Payment_logModel::getInstance()->insert($datapaylog);//执行添加支付表
            //订单状态走向表
            $orderRouteData = [];
            $orderRouteData['order_id'] = $request->input("out_trade_no");
            $orderRouteData['order_status'] = 20;
            $orderRouteData['create_time'] = time();
            $result4 = DB::table('order_route')->insert($orderRouteData);
            // 流程操作顺利则commit
            if ($result1 && $result2 && $result3 && $result4) {
                DB::commit();
                //激光推送
                $jpushObj = new JpushController();
                $canteenData = CanteenOwnerModel::getInstance()->getOwnerDataById($orderInfo['canteen_id']);
                for ($i = 1; $i <= 3; $i++) {
                    $flagSet = $jpushObj->pushMess($orderInfo['canteen_id'], $canteenData['phone'], '订单提醒', '来新订单啦，快点准备发货吧！', '1', 'order');
                    if ($flagSet == true) {
                        break;
                    }
                }
                //发送用户端激光推送
                $userJpush = new JpushYongController();
                for ($i = 1; $i <= 3; $i++) {
                    $flag = $userJpush->pushYongMess($userData['telephone'], '订单订单付款成功', "订单付款成功  您的订单{$orderInfo['order_id']}已付款完成，正等待商家配送。", '4');//1，2商家端  3，4，5用户端
                    if ($flag == true) {
                        break;
                    }
                }
                array_merge($orderInfo, array('flagSet' => $flagSet, 'flag' => $flag));
                $recordData = [];
                $recordData['order_id'] = $request->input("out_trade_no");
                $recordData['user_id'] = $orderInfo['user_id'];
                $recordData['user_name'] = $userData['user_name'];
                $recordData['trans'] = '';//交易号现在为空
                $recordData['pay_type'] = '';//支付类型0扣扣，1支付宝，2微信
                $recordData['pay_code'] = serialize($request->all());//此处是返回给客户端的数据
                $recordData['pay_fee'] = $request->input('pay_fee');//金额
                $recordData['status'] = 1;//0：去支付 1成功，2失败
                $recordData['create_time'] = time();
                $recordData['update_time'] = time();
                $recordData['pay_content'] = 'step2.支付金额0元成功！';
                payment_recordModel::instance()->insert($recordData);
                return response()->json(array('data' => [], 'message' => '支付成功！', 'code' => 200));
            } else {
                array_merge($orderInfo, array('result1' => $result1, 'result2' => $result2, 'result3' => $result3, 'result4' => $result4));
                $recordData = [];
                $recordData['order_id'] = $request->input("out_trade_no");
                $recordData['user_id'] = $orderInfo['user_id'];
                $recordData['user_name'] = $userData['user_name'];
                $recordData['trans'] = '';//交易号现在为空
                $recordData['pay_type'] = '';//支付类型0扣扣，1支付宝，2微信
                $recordData['pay_code'] = serialize($request->all());//此处是返回给客户端的数据
                $recordData['pay_fee'] = $request->input('pay_fee');//金额
                $recordData['status'] = 1;//0：去支付 1成功，2失败
                $recordData['create_time'] = time();
                $recordData['update_time'] = time();
                $recordData['pay_content'] = 'step2.支付金额0元失败！';
                payment_recordModel::instance()->insert($recordData);
                DB::rollBack();
                return response()->json(array('data' => $orderData, 'message' => '支付失败！', 'code' => 300));
            }
        }
    }
    /** @Author: YanLong-Rui
     *  @Modifications: 微信支付成功异步通知地址 订单状态的修改以这里为准
     *  @date:2017/9/10
     * @param Request $request String
     * @return Boolean
     */
    public function weChatpPayNotify(Request $request)
    {
        //接收参数
        $postStr = file_get_contents("php://input");
        //XML转数组
        $arrData = xmltoArray($postStr);
        //避免重复修改订单状态
        $orderInfo = Order::instance()->getOrderById($arrData["out_trade_no"]);
        //获取用户信息
        $userData = User::instance()->getUserById($orderInfo['user_id']);
        //判断订单是否合理
        if ($orderInfo['pay_fee'] != ($arrData['total_fee'] / 100) || empty($orderInfo)) {
            Blogger::__callStatic("notice", ['订单金额有误 或者订单不存在 7.', array('local_pay_fee' => $orderInfo['pay_fee'], 'wx_total_fee' => $arrData['total_fee'], 'wx_data' => $arrData), 'logs/wechat', date('Y-m-d')]);
        }
        //支付记录表
        $recordData = [];
        $recordData['order_id'] = $arrData["out_trade_no"];
        $recordData['user_id'] = $orderInfo['user_id'];
        $recordData['user_name'] = $userData['user_name'];
        $recordData['trans'] = $arrData["transaction_id"];//交易号现在为空
        $recordData['pay_type'] = 2;//支付类型0扣扣，1支付宝，2微信
        $recordData['pay_code'] = serialize($arrData);//此处是返回给客户端的数据
        $recordData['pay_fee'] = $arrData["total_fee"] / 100;//金额
        $recordData['status'] = -1;//0：去支付 1成功，2失败
        $recordData['create_time'] = time();
        $recordData['update_time'] = time();
        $recordData['pay_content'] = 'step2.微信支付异步回调成功';
        payment_recordModel::instance()->insert($recordData);
        $wechatobj = PaymentFactory::factory('Wechat');
        //已经处理过的订单//查询一次看看是否成功支付
        $arrayData = $wechatobj->wechatOrderinfo($arrData["out_trade_no"],'1');
        if ($orderInfo['is_pay'] == 1 && $arrayData['trade_state'] == 'SUCCESS') {
            echo "<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                </xml>";
            exit;
        }
        //验证签名
        $status = $wechatobj->checkSign($arrData);
        if ($status && $arrData['return_code'] == 'SUCCESS' && $arrData['result_code'] &&  $arrayData['trade_state'] == 'SUCCESS') {
            DB::beginTransaction();
            //处理逻辑
            /*$orderData['order_status'] = 20;
            $orderData['payed_at'] = time();//$arrData['time_end']
            $orderData['payment_id'] = 2;
            $orderData['is_pay'] = 1;//已付款
            $orderData['pay_type'] = 0;
            $orderData['updated_at'] = time();//$arrData['time_end']
            $result1 = Order::instance()->where("order_id", $arrData['out_trade_no'])->update($orderData);
            $orderData = array_merge($orderData, array('result1' => $result1));
            Blogger::__callStatic("notice", ['微信支付异步回调修改orders表成功！', [$orderData], 'logs/wechat', date('Y-m-d')]);*/
            //添加订单操作日志表
            $dataOrderLog = [];
            $dataOrderLog['operate_id'] = $orderInfo['user_id'];//用户id
            $dataOrderLog['operate_name'] = $userData['user_name'];//用户姓名
            $dataOrderLog['operateor_type'] = 2;// 操作平台 0,扣扣，1商家 2用户
            $dataOrderLog['order_id'] = $arrData['out_trade_no'];
            $dataOrderLog['type'] = 1;//1支付 2退款
            $dataOrderLog['pay_type'] = 2;//支付渠道 1支付宝 2微信
            $dataOrderLog['data'] = serialize($arrData);
            $dataOrderLog['created_at'] = time();
            Blogger::__callStatic("notice", ['微信支付异步回调添加orders_operate_log表成功！', [$dataOrderLog], 'logs/wechat', date('Y-m-d')]);
            $result2 = Orders_operate_logModel::getInstance()->insert($dataOrderLog);//执行添加订单日志表
            $dataOrderLog = array_merge($dataOrderLog, array('result2' => $result2));
            Blogger::__callStatic("notice", ['微信支付异步回调添加orders_operate_log表成功！', [$dataOrderLog], 'logs/wechat', date('Y-m-d')]);
            $datapaylog = [];
            $datapaylog['order_id'] = $arrData['out_trade_no'];
            $datapaylog['pay_code'] = serialize($arrData);
            $datapaylog['create_time'] = time();
            $datapaylog['update_time'] = time();
            $result3 = Payment_logModel::getInstance()->insert($datapaylog);//执行添加支付表
            $datapaylog = array_merge($datapaylog, array('result3' => $result3));
            Blogger::__callStatic("notice", ['微信支付异步回调添加Payment_log表成功！', [$datapaylog], 'logs/wechat', date('Y-m-d')]);
            //订单状态走向表
           /* $orderRouteData = [];
            $orderRouteData['order_id'] = $arrData['out_trade_no'];
            $orderRouteData['order_status'] = 20;
            $orderRouteData['create_time'] = time();
            $result4 = DB::table('order_route')->insert($orderRouteData);
            $orderRouteData = array_merge($datapaylog, array('result4' => $result4));*/



            //修改用户新客 最后下单时间
            User::instance()->updateNewCustomerLastOrderTime($orderInfo['user_id']);
            Blogger::__callStatic("notice", ['微信支付异步回调添加Payment_log表成功！', [$arrData], 'logs/wechat', date('Y-m-d')]);
            // 流程操作顺利则commit
            if (/*$result1 && */$result2 && $result3/* && $result4*/) {
                DB::commit();
                // 记帐
                event(new \App\Events\OrderPaid($recordData['order_id']));

                //待配送的订单要在20分钟后执行一次催单
                QueueEntrance::instance()->addQueue($orderInfo['order_id'],'6');
                $recordData = [];
                $recordData['order_id'] = $arrData["out_trade_no"];
                $recordData['user_id'] = $orderInfo['user_id'];
                $recordData['user_name'] = $userData['user_name'];
                $recordData['trans'] = $arrData["transaction_id"];//交易号现在为空
                $recordData['pay_type'] = 2;//支付类型0扣扣，1支付宝，2微信
                $recordData['pay_code'] = serialize($arrData);//此处是返回给客户端的数据
                $recordData['pay_fee'] = $arrData["total_fee"] / 100;//金额
                $recordData['status'] = 1;//0：去支付 1成功，2失败
                $recordData['create_time'] = time();
                $recordData['update_time'] = time();
                $recordData['pay_content'] = 'step3.微信支付修改订单状态成功';
                $recordData['open_id']  = $arrayData['openid'];
                $pay_id = payment_recordModel::instance()->insertGetId($recordData);
                event(new PayRiskControl($pay_id));
                echo $wechatobj->reply;
                exit;
            } else {
                $arrData = array_merge($arrData, array(/*'result1' => $result1,*/ 'result2' => $result2, 'result3' => $result3/*, 'result4' => $result4*/));
                $recordData = [];
                $recordData['order_id'] = $arrData["out_trade_no"];
                $recordData['user_id'] = $orderInfo['user_id'];
                $recordData['user_name'] = $userData['user_name'];
                $recordData['trans'] = $arrData["transaction_id"];//交易号现在为空
                $recordData['pay_type'] = 2;//支付类型0扣扣，1支付宝，2微信
                $recordData['pay_code'] = serialize($arrData);//此处是返回给客户端的数据
                $recordData['pay_fee'] = $arrData["total_fee"] / 100;//金额
                $recordData['status'] = 2;//0：去支付 1成功，2失败
                $recordData['create_time'] = time();
                $recordData['update_time'] = time();
                $recordData['pay_content'] = 'step4.微信支付修改订单状态失败！';
                payment_recordModel::instance()->insert($recordData);
                DB::rollBack();
            }
        } else {
            $recordData = [];
            $recordData['order_id'] = $arrData["out_trade_no"];
            $recordData['user_id'] = $orderInfo['user_id'];
            $recordData['user_name'] = $userData['user_name'];
            $recordData['trans'] = $arrData["transaction_id"];//交易号现在为空
            $recordData['pay_type'] = 2;//支付类型0扣扣，1支付宝，2微信
            $recordData['pay_code'] = serialize($arrData);//此处是返回给客户端的数据
            $recordData['pay_fee'] = $arrData["total_fee"] / 100;//金额
            $recordData['status'] = 2;//0：去支付 1成功，2失败
            $recordData['create_time'] = time();
            $recordData['update_time'] = time();
            $recordData['pay_content'] = 'step5.微信支付签名验证失败或者返回状态错误！';
            payment_recordModel::instance()->insert($recordData);
        }
    }
    /** @Author: YanLong-Rui
     *  @Modifications: 用于支付状态APP端回调查询
     *  @date:2017/9/10
     * @param Request $request String
     * @param order_id int
     * @return String
     */
    public function return_url(Request $request)
    {
        $clientData = $request->all();
        //查询订单是否支付成功
        $orderInfo = Order::instance()->getOrderById($clientData["order_id"]);
        if (empty($orderInfo)) {
            return response()->json(array('data' => [], 'message' => '订单不存在！', 'code' => 301));
        }
        if ($orderInfo['is_pay'] == 1 && $orderInfo['pay_fee'] == $clientData['total_amount']) {
            return response()->json(array('data' => ['payed_at' => $orderInfo['payed_at'], 'order_id' => $orderInfo['order_id'], 'pay_fee' => $orderInfo['pay_fee']], 'message' => '支付成功！', 'code' => 302));
        }
    }
    /** @Author: YanLong-Rui
     *  @Modifications: 微信退款成功通知地址
     *  @date:2017/9/10
     * @param Request $request String
     * @return Void
     */
    public function refundNotifyAction(Request $request)
    {
        //接收参数
        $postStr = file_get_contents("php://input");
        //xml转数组
        $arrData = xmltoArray($postStr);
        //记录日志
        Blogger::__callStatic("notice", ['微信退款成功异步通知1.', $arrData, 'logs/refundnotify', date('Y-m-d')]);
        if ($arrData['return_code'] == "SUCCESS") {
            try {
                $str = openssl_decrypt(($arrData['req_info']), 'AES-256-ECB', strtolower(md5("4e938ad0bad2e46b8cbb8e9b099a1e06")));
                $arr = xmltoArray($str);
                $order_no = $arr['out_trade_no'];
                $result = Orders_operate_logModel::getInstance()->addOrderOperate($arr,'2');//添加订单日志表
                if(!$result){
                    throw new \Exception('订单日志表添加失败！');
                }

                $activyResult =  Order::instance()->recoveryActivy($order_no);//退款后需要把活动中赠送的商品库存恢复
                if(!$activyResult){
                    throw new \Exception('退款活动中赠送的商品库存恢复失败！订单号：'.$order_no);
                }

                $goodsResult = Order::instance()->recoveryGoods($order_no);//退款后需要回复库存数量 恢复商品状态
                if(!$goodsResult){
                    throw new \Exception('退款后恢复商品库存数量失败！订单号：'.$order_no);
                }
                //修改订单表
                /*$result1 = DB::table('orders')->where('order_id', $order_no)->update([
                    'order_status' => '-20',
                    'apply_for_refund' => 2,
                    'updated_at' => time(),
                    'ok_refund_at' => time()
                ]);
                    if(!$result1){
                        throw new \Exception('订单表修改订单状态失败！订单号：'.$order_no);
                    }*/
                //记账
                event(new OrderRefunded($order_no));
                echo "<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                </xml>";
                exit;
            } catch (\Exception $e) {
                Blogger::__callStatic("notice", ['微信退款失败4.', [$e->getMessage()], 'logs/wechat_notify_refund', date('Y-m-d')]);
            }
        } else {
            Blogger::__callStatic("notice", ['微信退款异步通知数据异常！', [$arrData], 'logs/wechat_notify_refund', date('Y-m-d')]);
        }
    }

    /** @Author: YanLong-Rui
     *  @Modifications: 微信查询退款接口
     * 提交退款申请后，通过调用该接口查询退款状态。
     * 退款有一定延时，用零钱支付的退款20分钟内到账，
     * 银行卡支付的退款3个工作日后重新查询退款状态。
     *  @date:2017/9/10
     * @param Request $request String
     * @param order_id int
     * @return Void
     */
    public function checkIsRefund(Request $request)
    {
        $wechatobj = PaymentFactory::factory('Wechat');
        //组织数据
        $weChatData = $wechatobj->getSelectRefundData($request->input('order_id'));
        //请求查询退款接口
        $resultData = httpCurl('https://api.mch.weixin.qq.com/pay/refundquery', 'POST', $weChatData);
        echo "<pre>";
        print_r(XmltoArray($resultData));
        die;
    }
    /** @Author: YanLong-Rui
     *  @Modifications: 微信下载对账单功能
     *  @date:2017/9/10
     * @param Request $request String
     * @param date String
     * @return Void
     */
    public function downloadAccountStatement(Request $request)
    {
        $wechatobj = PaymentFactory::factory('Wechat');
        $weChatData = $wechatobj->accountStatement($request->input('date'));
        $resultData = httpCurl('https://api.mch.weixin.qq.com/pay/downloadbill', 'POST', $weChatData);
        echo "<pre>";
        file_put_contents(base_path() . "/storage/logs/AccountStatement/" . $request->input('date') . ".log", $resultData);
    }
}