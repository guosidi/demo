<?php

/**
 * Created by PhpStorm.
 * User: ryl
 * Date: 2017/7/24
 * Time: 15:05
 */

namespace App\PayLibaries;


use App\Tools\Blogger;
use App\Tools\Aes;
class Wechat
{
    public $reply;

    public function __construct(){
        $this->reply = "<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                </xml>";
        if(!defined('KEY')) {
            define("KEY","4e938ad0bad2e46b8cbb8e9b099a1e06");//key key设置路径：微信商户平台(pay.weixin.qq.com-->账户设置-->API安全-->密钥设置
        }
        if(!defined('APPID')) {
            define("APPID","wxb13d6c8a2ca4d7c3");//微信开放平台审核通过的应用APPID
        }
        if(!defined('MCHID')) {
            define("MCHID","1487219282");//微信支付分配的商户号
        }
        if(!defined('BODY')) {
            define("BODY",'抠抠网订单支付');//商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值。        define("BODY",'扣扣网支付中心');//
        }
        $serverName = isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'http://api-canteen.oomall.com';
        if(!defined('NOTIFY_URL')) {
            define("NOTIFY_URL",$serverName."/wechatnotify");//支付成功回调地址
        }
        if(!defined('TRADE_TYPE')) {
            define("TRADE_TYPE","APP");//支付类型
        }
        if(!defined('WECHATURL')) {
            define("WECHATURL","https://api.mch.weixin.qq.com/pay/unifiedorder");//微信统一下单地址
        }
    }

    //功能函数获取sign值
    protected function getSign($params){
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
           if (!empty($item)){         //剔除参数值为空的参数 退款操作中会产生使用券的字段是0的情况 而这里使用empty判断 会过滤等于0 的情况造成签名的不正确
            //if ($item!=''){         //剔除参数值为空的参数
                $newArr[] = $key.'='.$item;     // 整合新的参数数组
            }
        }

        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA."&key=".KEY;        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $params['sign'] = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $params;
    }
    //功能函数获取sign值 退款专用获取签名工具
    protected function getSignforRefund($params){
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            //if (!empty($item)){         //剔除参数值为空的参数 退款操作中会产生使用券的字段是0的情况 而这里使用empty判断 会过滤等于0 的情况造成签名的不正确
            if ($item!=''){         //剔除参数值为空的参数
                $newArr[] = $key.'='.$item;     // 整合新的参数数组
            }
        }

        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA."&key=".KEY;        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $params['sign'] = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $params;
    }

    /**
     * 拼装请求的数据
     * @return  String 拼装完成的数据
     */
    protected function setSendData($array){
        //定义模板
        $xml = "<xml>
                        <appid><![CDATA[%s]]></appid>
                        <body><![CDATA[%s]]></body>
                        <mch_id><![CDATA[%s]]></mch_id>
                        <nonce_str><![CDATA[%s]]></nonce_str>
                        <notify_url><![CDATA[%s]]></notify_url>
                        <out_trade_no><![CDATA[%s]]></out_trade_no>
                        <spbill_create_ip><![CDATA[%s]]></spbill_create_ip>
                        <total_fee><![CDATA[%d]]></total_fee>
                        <trade_type><![CDATA[%s]]></trade_type>
                        <sign><![CDATA[%s]]></sign>
                    </xml>";
        $data = [
            "appid"=>APPID,
            "mch_id"=>MCHID,
            "nonce_str"=>getRandStr(30,1),
            "body"=>BODY,
            "out_trade_no"=>$array['out_trade_no'],
            "total_fee"=>bcmul($array['pay_fee'],100,0),
            "spbill_create_ip"=>isset($array['clientIp']) ? $array['clientIp'] : getIp(),
            "notify_url"=>NOTIFY_URL,
            "trade_type"=>TRADE_TYPE,
        ];
        $arrData = $this->getSign($data);
        //xml赋值
        $xml = sprintf($xml, APPID, $arrData['body'], MCHID, $arrData['nonce_str'],NOTIFY_URL, $arrData['out_trade_no'], $arrData['spbill_create_ip'], $arrData['total_fee'], TRADE_TYPE, $arrData['sign']);
        //生成xml数据格式
        return $xml;
    }
    //order_id pay_fee clientIp 统一下单接口
    public function getWechatData($data){

        $dataforWechat = $this->setSendData($data);
        $result = httpCurl(WECHATURL,'POST',$dataforWechat);
        $wechatData = xmltoArray($result);
        return $wechatData;
    }

    public function getclientDataByPrepayId($prepayid){
        $data['appid'] = APPID;
        $data['partnerid'] = MCHID;//微信支付分配的商户号
        $data['package'] = 'Sign=WXPay';
        $data['noncestr'] = getRandStr(30,1);
        $data['timestamp'] = time();
        $data['prepayid'] = $prepayid;
        $wechatData = $this->getSign($data);
        return $wechatData;

    }
    public function refundcheckSign($signData){
        $sign = $signData['sign'];
        unset($signData['sign']);
        $data = $this->getSignforRefund($signData);
        if($sign == $data['sign']){
            return true;
        }else{
            return false;
        }
    }
    public function checkSign($signData){
        $sign = $signData['sign'];
        unset($signData['sign']);
        $data = $this->getSign($signData);
        if($sign == $data['sign']){
            return true;
        }else{
            return false;
        }
    }



    /**微信支付订单查询接口功能函数
     * @param int 1 客户端查询订单支付状态 2异步通知中查询订单支付状态
     */
    public function wechatOrderinfo($orderId,$type='1'){
        $data['appid'] = APPID;
        $data['mch_id'] = MCHID;
        $data['out_trade_no'] = $orderId;
        $data['nonce_str'] = getRandStr();


        $signData = $this->getSign($data);//获取sign值
        //定义xml字符串
        $xmlstr = "<xml>
   <appid><![CDATA[%s]]></appid>
   <mch_id><![CDATA[%s]]></mch_id>
   <nonce_str><![CDATA[%s]]></nonce_str>
   <out_trade_no><![CDATA[%s]]></out_trade_no>
   <sign><![CDATA[%s]]></sign>
</xml>";
        //xml赋值
        $xml = sprintf($xmlstr, $signData['appid'], $signData['mch_id'], $signData['nonce_str'], $signData['out_trade_no'],$signData['sign']);
        $orderInfoXml = httpCurl('https://api.mch.weixin.qq.com/pay/orderquery','POST',$xml);
        $arrayData = xmltoArray($orderInfoXml);
        if($arrayData['return_code']=='SUCCESS' && $arrayData['result_code']) {
            if ($type == '2'){
                Blogger::__callStatic("notice", ['异步通知中查询订单支付状态 返回数据2.', [$arrayData], 'logs/wechat', date('Y-m-d')]);
            } else {
                Blogger::__callStatic("notice", ['同步接口查询订单状态 返回数据1.', [$arrayData], 'logs/wechat', date('Y-m-d')]);
                return $arrayData;
            }
        }
    }

    //微信退款
    public function wechatRefund($arrData){
        $data['appid'] = APPID;
        $data['mch_id'] = MCHID;//微信支付分配的商户号
        $data['nonce_str'] = getRandStr(30,1);
        $data['out_trade_no'] = $arrData['order_id'];
        $data['out_refund_no'] = $arrData['order_id'];
        $data['total_fee'] = bcmul($arrData['total_fee'],100,0);//订单金额 微信以分为单位 bcmul($arrData['total_fee'],100,0),
        $data['refund_fee'] = bcmul($arrData['total_fee'],100,0);//订单退款金额 微信以分为单位
        $wechatData = $this->getSign($data);
        /*echo "<pre>";
        print_r($wechatData);exit;*/
        $xmlStr = "<xml>
                        <appid><![CDATA[%s]]></appid>
                        <mch_id><![CDATA[%s]]></mch_id>
                        <nonce_str><![CDATA[%s]]></nonce_str>
                        <out_trade_no><![CDATA[%s]]></out_trade_no>
                        <out_refund_no><![CDATA[%s]]></out_refund_no>
                        <total_fee><![CDATA[%d]]></total_fee>
                        <refund_fee><![CDATA[%d]]></refund_fee>
                        <sign><![CDATA[%s]]></sign>
                    </xml>";
        $xml = sprintf($xmlStr, $wechatData['appid'], $wechatData['mch_id'],$wechatData['nonce_str'],$wechatData['out_trade_no'], $wechatData['out_refund_no'], $wechatData['total_fee'], $wechatData['refund_fee'],$wechatData['sign']);
        return $xml;
    }


    public function getRefundData($arr){
        $data = base64_decode($arr);
        $key = md5(KEY);
        $aes = new Aes();
        $aes->setKey($key);
        // 解密
        $resultData = $aes->decode($arr);
        return $resultData;
    }

    public function getSelectRefundData($order_id){
        $data['appid'] = APPID;
        $data['mch_id'] = MCHID;//微信支付分配的商户号
        $data['nonce_str'] = getRandStr(30,1);
        $data['out_trade_no'] = $order_id;
        $selectRefundData = $this->getSign($data);

        $xmlStr = "<xml>
                    <appid><![CDATA[%s]]></appid>
                    <mch_id><![CDATA[%s]]></mch_id>
                    <nonce_str><![CDATA[%s]]></nonce_str>
                    <out_trade_no><![CDATA[%s]]></out_trade_no>
                    <sign><![CDATA[%s]]></sign>
                    </xml>";
        $xml = sprintf($xmlStr, $selectRefundData['appid'], $selectRefundData['mch_id'],$selectRefundData['nonce_str'],$selectRefundData['out_trade_no'], $selectRefundData['sign']);
        return $xml;
    }


    //下载对账单接口
    public function accountStatement($date){
        $data['appid'] = APPID;
        $data['mch_id'] = MCHID;//微信支付分配的商户号
        $data['nonce_str'] = getRandStr(30,1);
        $data['bill_date'] = $date;
        $data['bill_type'] = 'ALL';
        $stateData = $this->getSign($data);

        $xmlStr = "<xml>
                    <appid><![CDATA[%s]]></appid>
                    <mch_id><![CDATA[%s]]></mch_id>
                    <nonce_str><![CDATA[%s]]></nonce_str>
                    <bill_date><![CDATA[%s]]></bill_date>
                    <bill_type><![CDATA[%s]]></bill_type>
                    <sign><![CDATA[%s]]></sign>
                    </xml>";
        $xml = sprintf($xmlStr, $stateData['appid'], $stateData['mch_id'],$stateData['nonce_str'],$stateData['bill_date'], $stateData['bill_type'], $stateData['sign']);

        return $xml;
    }
}

