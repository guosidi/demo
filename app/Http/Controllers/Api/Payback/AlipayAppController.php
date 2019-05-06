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
class AlipayAppController extends Controller
{




    /** @Author: YanLong-Rui
     *  @Modifications:手动运行任务脚本
     *  @date:2017/9/15
     * @return void
     */
    public function test()
    {

    }
    /**
     *  @Author: YanLong-Rui
     *  @Modifications:支付宝统一下单接口
     *  @date:2017/7/24
     * 重要相关参数说明
     * $param $order_no int 订单号
     * $param $total_amount double 订单金额
     * $return String 客户端用于请求支付宝接口字符串
     */
    public function setParamsForAliPay(Request $request)
    {
        //获取支付宝支付类对象
        $alipayobj = PaymentFactory::factory('Alipay');
       
        $orderInfo = Order::getOrderByIdFirst($request->input('order_id'));
        if(!$orderInfo or $orderInfo->is_pay==1){
           return response()->json(array('data' =>'', 'message' => '订单已支付或者不存在，', 'code' => 300));
        }
        $data = $request->all();
        $data['total_amount']=$orderInfo->pay_fee;
        $data['subject']=$orderInfo->canteen_name;
        //生成请求参数
        $clientData = $alipayobj->getClientData($data);
      //print_r($alipayobj);die;
        if (!empty($clientData)) {
            //支付宝第一步获取用户输入 添加payment_record表
            $recordData = [];
            $recordData['order_id'] = $request->input('order_id');
            $recordData['user_id'] = $orderInfo->canteen_id;
            $recordData['user_name'] = $orderInfo->canteen_name;
            $recordData['trans'] = '';//交易号现在为空
            $recordData['pay_type'] = 2;//支付类型0扣扣，1支付宝，2微信
            $recordData['pay_code'] = serialize($clientData);//此处是返回给客户端的数据
            $recordData['pay_fee'] = $orderInfo->pay_fee;
            $recordData['status'] = 0;//0：去支付 1成功，2失败
            $recordData['create_time'] = time();
            $recordData['update_time'] = time();
            $recordData['pay_content'] = 'step1.支付宝支付返回客户端数据';

            //修改支付方式
            Order::instance()->where('order_id',$orderInfo->order_id)->update(['payment_id'=>'2','updated_at'=>time()]);
            //执行数据添加
            payment_recordModel::instance()->insert($recordData);
            //返回客户端数据
            return response()->json(array('data' => $clientData, 'message' => '调用成功！', 'code' => 200));
        }
    }

    /**
     *  @Author: YanLong-Rui
     *  @Modifications:支付宝异步通知结果回调地址 订单最终生效以这里为准
     *  @date:2017/7/24
     * 重要相关参数说明 此参数主要是支付宝返回参数内容
     * [gmt_create] => 2017-09-05 15:28:50
    [charset] => UTF-8
    [seller_email] => linmeifen@kouclo.com
    [subject] => 测试支付
    [sign] => Fu2qe2rP828G1S3aYwbQv6yfZYMYGIphd6gvRUETgz23uyBlkWamvNvR+3WZMDwiGnm/RGzWvB5V4mMyp6hhL/wNlHMOK55YMBLymncAy50wOeeX7tk4u/JvoCC5QxFW5VBytk3v4Ek34AgC2gOPHo/aRpiMiz3ssh6N5uFuxvUEoDm1HZr8A1KTLeKEMePSEkf6OM1Eexo2/xq3hLch/FUorWR7YaRNIV88XlWrxzkUjsJlexolRYMO8SPr/9nBM8y14DVbzsOQglkDTmbAl9dBpHjT3ilCg69PFJz+YGYvixHbHferZvyC0N1haoUMrbZC4d7UNz6CkL0iu0/BBw==
    [buyer_id] => 2088302050040147
    [invoice_amount] => 0.02
    [notify_id] => 522fcfa7e0f8237e9096de5286151d6h2y
    [fund_bill_list] => [{"amount":"0.02","fundChannel":"ALIPAYACCOUNT"}]
    [notify_type] => trade_status_sync
    [trade_status] => TRADE_SUCCESS
    [receipt_amount] => 0.02
    [app_id] => 2017071907808204
    [buyer_pay_amount] => 0.02
    [sign_type] => RSA2
    [seller_id] => 2088121176758875
    [gmt_payment] => 2017-09-05 15:28:51
    [notify_time] => 2017-09-05 15:28:51
    [version] => 1.0
    [out_trade_no] => 115045965206820
    [total_amount] => 0.02
    [trade_no] => 2017090521001004140219505954
    [auth_app_id] => 2017071907808204
    [buyer_logon_id] => 137****8334
    [point_amount] => 0.00
     * $return Void
     */
    public function alipayNotify(Request $request)
    {
        //接收参数
        $alipayData = $request->all();
        Blogger::__callStatic("notice", ['支付宝回调所有数据写入日志.', [$alipayData], 'logs/alipay_refund', date('Y-m-d')]);
      
        //支付宝的退款通知会触发这里，现增加判断，退款业务逻辑处理在同步中进行处理
        if ($alipayData['trade_status'] == 'TRADE_CLOSED') {
            Blogger::__callStatic("notice", ['支付宝退款异步通知不做处理0.', [$alipayData], 'logs/alipay_refund', date('Y-m-d')]);
            echo "success";
            exit;
        }
   
        //获取订单信息
        $orderInfo = Order::getOrderByIdFirst($request->input('out_trade_no'));
     
        //支付宝第二步支付宝回调成功 添加payment_record表
        $recordData = [];
        $recordData['order_id'] = $orderInfo->order_id;
        $recordData['user_id'] = $orderInfo->canteen_id;
        $recordData['user_name'] = $orderInfo->canteen_name;
        $recordData['trans'] = $alipayData['trade_no'];//交易号现在为空
        $recordData['pay_type'] = 2;//支付类型0扣扣，1支付宝，2微信
        $recordData['pay_code'] = serialize($alipayData);//此处是返回给客户端的数据
        $recordData['pay_fee'] = isset($alipayData['receipt_amount'])? $alipayData['receipt_amount']: 0;//实收金额
        $recordData['status'] = -1;// -1成功回调还没有修改订单状态验证签名 0：去支付 1成功，2失败
        $recordData['create_time'] = time();
        $recordData['update_time'] = time();
        $recordData['pay_content'] = 'step2.支付宝支付异步通知成功';
        $recordData['open_id'] = $alipayData['buyer_id'];
        $pay_id = payment_recordModel::instance()->insertGetId($recordData);
        //event(new PayRiskControl($pay_id));
       // error_log(date("Y-m-d H:i:s")."====00000order_id"."-======".json_encode($orderInfo)."\n",3,base_path("storage/logs")."/Alipay/111errororder".date("Y-m-d").".log");
        
        //处理逻辑 获取支付宝类对象
        $alipayobj = PaymentFactory::factory('Alipay');
        //    //查询订单的支付状态
        //$queryData = $alipayobj->tradeQuery($alipayData['out_trade_no']);
        // if ($flag && $queryData['trade_status'] == 'TRADE_SUCCESS') {  以后处理校验
        //验证签名
        $flag = $alipayobj->checkRsa($alipayData);
        if($flag){
            
        }else{
         //   error_log(date("Y-m-d H:i:s")."====flagerror---".$flag."----======".json_encode($orderInfo)."\n",3,base_path("storage/logs")."/Alipay/111errororder".date("Y-m-d").".log");
            
        }
     
            $queryData['trade_status'] = 'TRADE_SUCCESS';
            
            DB::beginTransaction();
            
            //已经处理过的订单不做处理
            if ($orderInfo->is_pay == 1 && $queryData['trade_status'] == 'TRADE_SUCCESS') {
                $array = ['已经完成的订单!', [$orderInfo], 'logs/Alipay', date('Y-m-d'),];
                Blogger::__callStatic("notice", $array);
                error_log(date("Y-m-d H:i:s")."====222order_id"."-======".json_encode($queryData)."\n",3,base_path("storage/logs")."/Alipay/111errororder".date("Y-m-d").".log");
                echo "success";
             exit;
            }
          
            //订单相关参数检查
            if ((empty($orderInfo) || $orderInfo->pay_fee != $alipayData['total_amount'] || $alipayData['app_id'] != '2018101461694202')) {
                $array = ['参数篡改!', ['order_id' => $alipayData['out_trade_no'], 'total_amount' => $alipayData['total_amount'], 'app_id' => $alipayData['app_id'], 'trade_status' => $alipayData['trade_status']], 'logs/Alipay', date('Y-m-d')];
                Blogger::__callStatic("notice", $array);
                
            }
         
          if($alipayData['trade_status']=='TRADE_SUCCESS'){
            $result1= Order::instance()->updateOrderPayed(trim($alipayData['out_trade_no']),2,$alipayData['trade_no']);
            //修改订单状态.
            if( $result1==0){
                $result1=false;
                //记录日志修改失败 order_id
                $array = [date("Y-m-d H:i:s").'修改订单!失败', [$orderInfo], 'logs/UpdateAlipayOrderFalse', date('Y-m-d'),];
                Blogger::__callStatic("notice", $array);
            }
            //支付日志表
            $datapaylog = [];
            $datapaylog['order_id'] = $alipayData['out_trade_no'];
            $datapaylog['pay_code'] = serialize($alipayData);
            $datapaylog['create_time'] = strtotime($alipayData['notify_time']);
            $datapaylog['update_time'] = strtotime($alipayData['notify_time']);
            $result3 = Payment_logModel::getInstance()->insert($datapaylog);//执行添加支付表
            
            if ($result1 && $result3  && ($alipayData['trade_status'] == 'TRADE_FINISHED' || $alipayData['trade_status'] == 'TRADE_SUCCESS')) {
                DB::commit();
                
                // 记帐 可用可不用
              //  event(new \App\Events\OrderPaid($recordData['order_id']));
                //记录文件日志
                Blogger::__callStatic("notice", ['支付宝支付成功，已提交事务2.', [$alipayData], 'logs/Alipay', date('Y-m-d')]);
                //支付宝第3步支付宝回调成功 添加payment_record表
                $recordData = [];
                $recordData['order_id'] = $orderInfo->order_id;
                $recordData['user_id'] = $orderInfo->canteen_id;
                $recordData['user_name'] = $orderInfo->canteen_name;
                $recordData['trans'] = $alipayData['trade_no'];//交易号
                $recordData['pay_type'] = 1;//支付类型0扣扣，1支付宝，2微信
                $recordData['pay_code'] = serialize($alipayData);//支付宝回调数据
                $recordData['pay_fee'] = isset($alipayData['receipt_amount'])? $alipayData['receipt_amount']: 0;//实收金额
                $recordData['status'] = 1;//0：去支付 1成功，2失败
                $recordData['create_time'] = time();
                $recordData['update_time'] = time();
                $recordData['pay_content'] = 'step3.支付宝支付成功成功修改订单状态！';
                payment_recordModel::instance()->insert($recordData);
                echo "success";
               exit;
            } else {
                
                $alipayData = array_merge($alipayData, array('result1' => $result1, 'result3' => $result3));

                //记录文件日志
                Blogger::__callStatic("notice", ['支付宝支付返回参数错误！.', [$alipayData], 'logs/Alipay', date('Y-m-d')]);
                //支付宝第3步支付宝回调成功 添加payment_record表
                $recordData = [];
                $recordData['order_id'] = $orderInfo->order_id;
                $recordData['user_id'] = $orderInfo->canteen_id;
                $recordData['user_name'] = $orderInfo->canteen_name;
                $recordData['trans'] = $alipayData['out_trade_no'];//交易号现在为空
                $recordData['pay_type'] = 1;//支付类型0扣扣，1支付宝，2微信
                $recordData['pay_code'] = serialize($alipayData);//支付宝回调数据和修改订单表的所有状态
                $recordData['pay_fee'] = isset($alipayData['receipt_amount'])? $alipayData['receipt_amount']: 0;//实收金额
                $recordData['status'] = 2;//0：去支付 1成功，2失败
                $recordData['create_time'] = time();
                $recordData['update_time'] = time();
                $recordData['pay_content'] = 'step4.支付宝支付回调修改订单状态失败或者返回状态错误！';
                payment_recordModel::instance()->insert($recordData);
                DB::rollBack();
            }
        } else {
            
            $alipayData = array_merge($alipayData, array('flagResult' => $flag));
            //记录文件日志
            Blogger::__callStatic("notice", ['支付宝异步返回中签名验证错误！.', [$alipayData], 'logs/Alipay', date('Y-m-d')]);
            //支付宝第3步支付宝回调成功 添加payment_record表
            $recordData = [];
        
            $recordData['order_id'] = $orderInfo->order_id;
            $recordData['user_id'] = $orderInfo->canteen_id;
            $recordData['user_name'] = $orderInfo->canteen_name;
            $recordData['trans'] = $alipayData['out_trade_no'];//交易号现在为空
            $recordData['pay_type'] = 1;//支付类型0扣扣，1支付宝，2微信
            $recordData['pay_code'] = serialize($alipayData);//支付宝回调数据和修改订单表的所有状态
            $recordData['pay_fee'] = isset($alipayData['receipt_amount'])? $alipayData['receipt_amount']: 0;//实收金额
            $recordData['status'] = 2;//0：去支付 1成功，2失败
            $recordData['create_time'] = time();
            $recordData['update_time'] = time();
            $recordData['pay_content'] = 'step5.支付宝支付签名验证失败！';
            payment_recordModel::instance()->insert($recordData);
        }
    }


    /**
     *  @Author: YanLong-Rui
     *  @Modifications:功能函数 安卓传递过来的参数转化成想要的格式
     *  @date:2017/7/28
     * 重要相关参数说明
     * $param $data array 要格式化的数组
     * $return array 客户端用于请求支付宝接口字符串
     */
    protected function formatData($data)
    {
        $tempData = json_decode($data, true);
        $arr['result'] = json_decode($tempData['result'], true);
        $arr['result']['alipay_trade_app_pay_response']['sign'] = $arr['result']['sign'];
        $arr['result']['alipay_trade_app_pay_response']['sign_type'] = $arr['result']['sign_type'];
        unset($arr['result']['sign']);
        unset($arr['result']['sign_type']);
        $arr['memo'] = ($tempData['memo']);
        $arr['resultStatus'] = ($tempData['resultStatus']);
        return $arr;
    }

    /**@Author: YanLong-Rui
     *  @Modifications:获取token 此处只执行一次 以后token进行定时刷新使用定时任务自动更新
     *  @date:2017/7/28
     * @param Request $request
     */
    public function getAppAuthToken(Request $request)
    {
        $alipayobj = PaymentFactory::factory('Alipay');
        $codeData = $request->all();
        $tokenData = $alipayobj->getToken($codeData);

        if ($tokenData) {
            echo "<pre>";
            echo "Token 获取成功！<br/>";
            print_r($tokenData);
            exit;
        } else {
            echo "token令牌获取失败！";
        }
    }

    /**统一收单交易撤销接口
     * 支付交易返回失败或支付系统超时，
     * 调用该接口撤销交易。
     * 如果此订单用户支付失败，
     * 支付宝系统会将此订单关闭；
     * 如果用户支付成功，支付宝系统会将此订单资金退还给用户。
     * 注意：只有发生支付系统超时或者支付结果未知时可调用撤销，
     * 其他正常支付的单如需实现相同功能请调用申请退款API。
     * 提交支付交易后调用【查询订单API】，没有明确的支付结果再调用【撤销订单API】。
     */
    public function cancel($orderId)
    {
        $alipayobj = PaymentFactory::factory('Alipay');
        $responseData = $alipayobj->aliCcancel($orderId);
    }


    /**
     * *  @Author: YanLong-Rui
     *  @Modifications:支付宝同步通知接口
     *  @date:2017/7/28
     * 重要相关参数说明
     * @param Request $request
     * @param orderinfo String
     * @return \Illuminate\Http\JsonResponse
     */
    public function alipayReturnUrl(Request $request)
    {
        //接收处理参数
        $responData = $this->formatData($request->input("orderinfo"));

        //检查订单
        $orderInfo = Order::getOrderByIdFirst($responData['result']['alipay_trade_app_pay_response']['out_trade_no']);
        if (empty($orderInfo)) {
            return response()->json(array('data' => [], 'message' => '订单不存在！', 'code' => 301));
        }

        //接口调用是否成功
        $orderflag = [0 => '待付款订单',25 => '商家派单中', 20 => '已付款待配送', 30 => '配送中（已发货）', 40 => '确认收货订单完成', -10 => '已取消', -20 => '付款后已退款', 50 => '已删除', 60 => '付款后退款中', 70 => '配送后退款中'];
        if (($orderInfo['payment_id'] == 0) && ($responData['resultStatus'] == 9000 || $responData['resultStatus'] == 8000 || $responData['resultStatus'] == 6004)) {
            return response()->json(array('data' => $responData, 'message' => '用户支付成功！', 'code' => 200));
        } else {
            return response()->json(array('data' => ['order_status' => $orderInfo['order_status'], 'resultStatus' => $responData['resultStatus']], 'message' => $orderflag[$orderInfo['order_status']], 'code' => 300));
        }
    }

    /** @Author: YanLong-Rui
     *  @Modifications:日志查看功能函数
     *  @date:2017/8/15
     * @param Request $request
     * @param $path string
     * @return void
     */
    public function crontab(Request $request)
    {
        $file = $request->input("path");
        $path = base_path('storage/logs/') . $file;
        $logData = file_get_contents($path);
        echo "<pre>";
        print_r($logData);

    }



    /**
     * @param Request $request
     * 账单时间：日账单格式为yyyy-MM-dd，月账单格式为yyyy-MM。
     */
        public function getSatementUrl(Request $request){
            $alipayobj = PaymentFactory::factory('Alipay');
            $arrData = $alipayobj->getDownloadStatementUrl();
            if($arrData['code'] == 10000){
                echo "<a href=".$arrData['bill_download_url']." target=_blank>点击下载支付宝对账单</a>";
            }else{
                echo "code：".$arrData['code']."<br/>msg：".$arrData['msg'];
            }
        }
}

