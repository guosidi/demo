<?php
/**
 * Created by PhpStorm.
 * @Author: YanLong-Rui
 * Date: 2017/7/24
 * Time: 19:24
 */
namespace App\Http\Controllers\Payback;

use App\Http\Controllers\Controller;
use App\Tools\XmlTool;
use App\Tools\Blogger;
use App\Models\Order;
use App\Tools\PaymentFactory;
use App\Models\Orders_operate_logModel;
use Illuminate\Support\Facades\DB;
use App\Events\OrderRefunded;
class RefundController extends Controller
{


    private static $instance;

    public static function instance()
    {
        if ( !self::$instance )
            self::$instance = new RefundController();

        return self::$instance;
    }

    /**
     *  @Author: YanLong-Rui
     *  @Modifications:支付宝线上下单交易最多3个月可以原路退款   微信是一年内的订单可以原路退款
     *  @date:2017/7/24
     * 重要相关参数说明
     * $param $order_no int 订单号
     * $return boolean
     */
    public function refund($order_no)
    {
            $orderInfo = Order::instance()->getOrderById($order_no);
            if (empty($orderInfo)) {
                return false;
            }
            switch($orderInfo['payment_id']){
                case '1'://支付宝退款
                    return $this->alipayRefund($orderInfo);
                case '2'://微信退款
                    return $this->wechatRefund($orderInfo);
                default :
                    return false;
            }
    }

    //支付宝退款流程
    public function alipayRefund($orderInfo){
        try {

            $order_no = $orderInfo['order_id'];
            $alipayobj = PaymentFactory::factory('Alipay');//获取支付宝类对象
            $responseData = $alipayobj->refund(['order_id' => $orderInfo['order_id'], 'pay_fee' => $orderInfo['pay_fee']]);//调用退款接口 并返回数据

            //判断退款是否成功
            if ($responseData['alipay_trade_refund_response']['code'] == "10000" && $responseData['alipay_trade_refund_response']['fund_change'] == "Y") {
                Blogger::__callStatic("notice", ['支付宝退款同步返回数据成功！5.', [$responseData], 'logs/alipay_refund', date('Y-m-d')]);
                $resultOperate = Orders_operate_logModel::getInstance()->addOrderOperate($responseData,1);//添加订单日志表 1 支付宝
                if (!$resultOperate) {
                    throw new \Exception("Orders_operate_log表修改失败" . $order_no);
                }

                $activyResult = Order::instance()->recoveryActivy($order_no);//退款后需要把活动中赠送的商品库存恢复
                if (!$activyResult) {
                    throw new \Exception("优惠活动，恢复赠送商品库存失败！订单号：" . $order_no);
                }

                $goodsResult = Order::instance()->recoveryGoods($order_no);//退款后需要回复商品库存数量
                if (!$goodsResult) {
                    throw new \Exception('退款后恢复商品数量失败！订单号：' . $order_no);
                }

                /*//修改订单表
                $result3 = DB::table('orders')->where('order_id', $order_no)->update([
                    'order_status' => '-20',
                    'apply_for_refund' => 2,
                    'updated_at' => time(),
                    'ok_refund_at' => time()
                ]);
                if (!$result3) {
                    throw new \Exception("修改订单状态失败！订单号：" . $order_no);
                }*/
                event(new OrderRefunded($order_no));
                return true;
            } else {

                sendOne('18859518732','抠抠小卖部支付宝退款'.$responseData['alipay_trade_refund_response']['sub_msg']);
                Blogger::__callStatic("notice", ['支付宝退款异常！', [$responseData], 'logs/alipay_refund', date('Y-m-d')]);
                throw new \Exception($responseData['alipay_trade_refund_response']['sub_msg']);
            }
        }catch(\Exception $e){
            Blogger::__callStatic("notice", ['支付宝退款异常.', [$e->getMessage()], 'logs/alipay_refund', date('Y-m-d')]);
            return false;
        }
    }
        //微信退款
        public function wechatRefund($orderInfo){
            $wechatObj = PaymentFactory::factory('Wechat'); //获取微信类对象
            $result = $wechatObj->wechatRefund(['order_id' => $orderInfo['order_id'], 'total_fee' => $orderInfo['pay_fee']]); //获取退款请求数据

            Blogger::__callStatic("notice", ['微信手动退款组装请求数据1.', [XmlTool::readXml($result)], 'logs/wechat_refund', date('Y-m-d')]);//记录日志便于错误追踪
            $wechatReturnData = httpCurl('https://api.mch.weixin.qq.com/secapi/pay/refund', 'POST', $result, true);//请求退款接口
            $arrData = XmlTool::readXml($wechatReturnData); //xml转数组
            Blogger::__callStatic("notice", ['微信手动退款返回数据 2.', [$arrData], 'logs/wechat_refund', date('Y-m-d')]);

            //验证签名
            $status = $wechatObj->refundcheckSign($arrData);
            //判断返回码
            if ($status && $arrData['return_code'] == 'SUCCESS' && $arrData['result_code'] == 'SUCCESS') {
                return true;
            } else {
                sendOne('18859518732','抠抠小卖部微信支付异常订单号'.$arrData['out_trade_no']);
                Blogger::__callStatic("notice", ['微信退款失败', [$arrData], 'logs/wechat_refund', date('Y-m-d')]);
               return false;
            }
        }
}