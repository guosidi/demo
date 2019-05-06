<?php

/**
 * Created by PhpStorm.
 * User: YanLong-Rui
 * Date: 2018/5/08
 * Time: 11:45
 * 通联收银宝商户接入 扫码支付功能类 主扫类型
 */

namespace App\PayLibaries;


class UnionorderActive
{

    const UNION_CUSID = "1487219282";//微信支付分配的商户号
    const UNION_KEY = "4e938ad0bad2e46b8cbb8e9b099a1e06";//keykey设置路径：微信商户平台(pay.weixin.qq.com-->账户设置-->API安全-->密钥设置
    const UNION_APPID = "wxb13d6c8a2ca4d7c3";//微信开放平台审核通过的应用APPID
    const UNION_VERISION = "wxb13d6c8a2ca4d7c3";//微信开放平台审核通过的应用APPID
    const BODY = "抠抠网订单支付";//商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值
    const NOTIFY_URL = "ytest-api-canteen.oomall.com/unionorder";//统一下单接口异步通知接口

    //功能函数获取sign值
    protected function getSign($params){
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)){         //剔除参数值为空的参数
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
    // munionorder 统一下单接口
    public function pay($data){
        $params = [];
        $params["cusid"] = config('wechat.mechid');//商户号	平台分配的商户号	否	15
        $params["appid"] = AppConfig::APPID;//应用ID	平台分配的APPID	否	8
        $params["version"] = AppConfig::APIVERSION;//版本号	接口版本号	可	2	默认填11
        $params["trxamt"] = "1";//交易金额	单位为分	否	15
        $params["reqsn"] = "123456";//订单号,自行生成     商户交易单号	商户的交易订单号	否	32	保证商户平台唯一
        $params["paytype"] = "W01";//交易方式	详见附录3.3 交易方式	否	3   W01：微信扫码支付 A01：支付宝扫码支付 Q01：手机QQ扫码支付
        $params["randomstr"] = "1450432107647";//随机字符串	商户自行生成的随机字符串	否	32
        $params["body"] = "商品名称";//订单标题	订单商品名称，为空则以商户名作为商品名称	是	100	最大100个字节(50个中文字符)
        $params["remark"] = "备注信息";
        $params["acct"] = "";
        $params["limit_pay"] = "no_credit";//支付限制	no_credit--指定不能使用信用卡支付	是	32	暂时只对微信支付有效,仅支持no_credit
        $params["notify_url"] = "http://172.16.2.46:8080/vo-apidemo/OrderServlet";
        $params["sign"] = AppUtil::SignArray($params,AppConfig::APPKEY);//签名

        $paramsStr = AppUtil::ToUrlParams($params);//url传参拼接
        $url = AppConfig::APIURL . "/pay";
        $rsp = request($url, $paramsStr);
        echo "请求返回:".$rsp;
        echo "<br/>";
        $rspArray = json_decode($rsp, true);
        if(validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }
    }

}

