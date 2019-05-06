<?php
/**
 * Created .
 * User: ryl
 * Date: 2017/9/20
 * Time: 14:49
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/*use App\Models\Order;
use App\Models\User;*/
class payment_recordModel extends Model
{
    private $arr = [0=>'kouclo',1=>'wechat',2=>'alipay','W01'=>3 ,'A01'=>4 ,'Q01'=>5];//支付类型0扣扣，1支付宝，2微信 3微信扫一扫 4支付宝扫一扫 5QQ扫一扫
    protected $table = "payment_record";

    private static $instance;

    protected $dateFormat = 'U';//时间格式为int类型
    public static function instance()
    {
        if (!self::$instance) self::$instance = new payment_recordModel();
        return self::$instance;
    }

    /**扫码支付请求统一下单 新增payment_record表
     * @param $data
     * 新增paymentrecord表
     */
    public function recordPaymentData($data,$type){
        $orderInfo = Order::instance()->getOrderById($data['order_id']);
        $userData = User::instance()->getUserById($orderInfo['user_id']);
        $recordData = [];
        $recordData['order_id'] = $data['order_id'];
        $recordData['user_id'] = $orderInfo['user_id'];
        $recordData['user_name'] = $userData['user_name'];
        $recordData['trans'] = '';//交易号现在为空
        $recordData['pay_type'] = $this->arr[$type];//支付类型0扣扣，1支付宝，2微信
        $recordData['pay_code'] = serialize($data);//此处是返回给客户端的数据
        $recordData['pay_fee'] = $orderInfo['pay_fee']*100;//金额
        $recordData['status'] = 0;//0：去支付 1成功，2失败
        $recordData['create_time'] = time();
        $recordData['update_time'] = time();
        $recordData['pay_content'] = 'step1.微信扫一扫支付返回客户端二维码';
        return payment_recordModel::instance()->insert($recordData);
    }

    /**扫码支付异步通知中新增payment_record表
     * @param $data
     * @return bool
     */
    public function notifyInsert($data){
       return payment_recordModel::instance()->insert($data);
    }
}