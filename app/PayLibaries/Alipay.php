<?php
/**
 * Created by PhpStorm.
 * User: ryl
 * Date: 2017/7/24
 * Time: 19:24
 */

namespace App\PayLibaries;


use AopClient;//引入主类
use AlipayTradeAppPayRequest;//支付类
use AlipayOpenAuthTokenAppRequest;//获取token类 退款需要token
use AlipayTradeCancelRequest;//统一收单交易撤销接口
use AlipayTradeRefundRequest;
use AlipayDataDataserviceBillDownloadurlQueryRequest;
use AlipayTradeQueryRequest;
use App\Models\CacheModel;
use App\Tools\Blogger;
class Alipay
{
    public function __construct(){

        $alipay_path =  dirname(__DIR__);
        include($alipay_path . "/Alipay/AopSdk.php");

        if(!defined('FORMAT')) {
            define("FORMAT","json");//数据格式
        }
        if(!defined('CHARSET')) {
            define("CHARSET",'UTF-8');//字符集
        }
        if(!defined('SIGNTYPE')) {
            define("SIGNTYPE",'RSA2');//签名类型
        }
        if(!defined('GATWAYURL')) {
            define("GATWAYURL",'https://openapi.alipay.com/gateway.do');//支付宝网关
        }
        /*  */
        if(!defined('APPID')) {
            define("APPID",'2018101461694202');//appid
        }
        if(!defined('ALIPAYRSAPUBLICKEY')) {
            //支付宝公钥
            define("ALIPAYRSAPUBLICKEY",'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5Cl+bLPIcZdEaT5KHGW86JjLlG6mXchaoxJXh3Pv68u4+l3tYhurGRX7majxuoC7hLvlp29JZnDG6gVWt7U/tMHcUpKiLleEQUNA5mjsPenlUIeTvLwIfhUgPU6PY17hktR53eCD+sYm5VZWY1HjRSr9zDu1m2fI1nSv0wIc7avSQABd02BgfYfRtOO9S2g+od2amjlLMf4NsP9kXPAIrDJLE69Otg4XOn+V+BHfHXylaucy5AZ+P/j8jUvvKNgGtCsAMc4GU8sLGJ7Z5T+VCsEgiEj2/ntwaBnzfHOD6yvmMpplwydcXVwP+P1MqcVGYpZ1LqICekGQV15cYBW/3wIDAQAB');
            
        }
        if(!defined('RSAPRIVATEKEY')) {
            //支付宝公钥
            define("RSAPRIVATEKEY",'MIIEpAIBAAKCAQEA5Cl+bLPIcZdEaT5KHGW86JjLlG6mXchaoxJXh3Pv68u4+l3tYhurGRX7majxuoC7hLvlp29JZnDG6gVWt7U/tMHcUpKiLleEQUNA5mjsPenlUIeTvLwIfhUgPU6PY17hktR53eCD+sYm5VZWY1HjRSr9zDu1m2fI1nSv0wIc7avSQABd02BgfYfRtOO9S2g+od2amjlLMf4NsP9kXPAIrDJLE69Otg4XOn+V+BHfHXylaucy5AZ+P/j8jUvvKNgGtCsAMc4GU8sLGJ7Z5T+VCsEgiEj2/ntwaBnzfHOD6yvmMpplwydcXVwP+P1MqcVGYpZ1LqICekGQV15cYBW/3wIDAQABAoIBAHAtrokHg7/Fnc8Y3xtDMjQyG7XpCUcG4PlYZUylxgpq6ZG/aJ1Z+S+mTUPpeqA19vx13Z4K603AmSwqX86HAMGApzC6A59BTDdCN8CRScXPH/4OCqc60/oZrPY3j+xNlB669QgQARPlJO6RGnxOoK07S/mESfAUgPxf9qqKWWrIp/MEvxztIwWtIA4aLtLHLMyGAsPDEiDot5PJPNybKsY2BOT9c7DHarnSKtt+k1Kq7r9Q7EYO/ApQQd+cgjkKeME5Yt5R+lnlcvMk3DlX1Kc2c8hp+5yy84sy9Wk+FDQ4orkBuc9dVruV9p8Y21DmN+vk6X9gAv/NWs2ZzRhRI9ECgYEA/ZtxpAd4zKToJEnDL1clX4JkmCWLkELK3dJFEBcke4HuYJxs/Mt6mN09/IUpwasxEKRNTMS03uhLdfoZez2S0QsiexEVJBRmzuykjVwXtRajflrUUmw5OEdL7KAcYXplTMxeiwag2uSI7NZZA0keNbuJlIDxnNmO2iQScGeqfSsCgYEA5lCXKYUhLacUClDwXpAv/3YEjyVDTSsEqGvzGSHWG25czm2km9FZrLiFHBC+hmeFG1QN8fPRjNYkYS/6dWZ9beF061VHYLsspVt5R3MNe2ED8p7c3RX25lu0Q7+p1ErTCI0Uqe4n6cfrIQ+saz2WbASOEPcWeEimbJNUWovbth0CgYEA6esT4ckmihPdL4N97k+CYFskMBYJafHBruLA4vuFogoRdrkx6eZBWXhQMXgeMM2XfxMdXZ7eefBnkhUypbMAlaKglkUGa/YJcfliOiTklRqhiO18g93g1WElzcdNBjhf8XtcBP4DJoO/LDz1c4tguw0U9I4Oza76DYlSHK3x2msCgYEA3byJTDgYEVPiP2xdfy1iiJifGxHNEeZwvZXwnimtcQt33mZBLKMJqvibbsHQLKZNyFnMcz1Cak1hCRCgGZdq+vctEcUwyzhpa2n+AYQLXtWwb03zZgdXhcCUdQV6BCFlu73YPyJC2xjWjyX/4d+Rir136W15/KQ1ViMh+2rbRSECgYAmR/0S+Es0nZZKYWPiWOAswAuuJHtd4caInjtg6+Mmaa3dCjLSVML9vdD7YfmgKRMvRsQtHsrDXQ0ATJ5Sg4xUi0Qh0zK688HEL3mF8+sD1CzsTSeiD+RGRt0D6ijjITCvp5gwAmkrymdQ5ks4lqbiTpqn7KIdNMT8q76ZjKncvw==');//
            
        }
        
   /*      
      if(!defined('APPID')) {
            define("APPID",'2018101361695116');//appid
        }
        if(!defined('ALIPAYRSAPUBLICKEY')) {
            //支付宝公钥
            define("ALIPAYRSAPUBLICKEY",'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvW4V5V0Q7ofWMayhumlwbpV0BLa8f0EARtFz9YE4X3UYFnNoM2TQt1rBCaam3UnoEDXBY7L5J4ejLrpnwrXbfa7xuzqyjznpqpMSPQG593m9ToBWgaRSDU+C+cyDejsV8piNiiigkkaUTx1gvIXd/RwTiqg+zOAopAkFLpmdmyXK/sMN0V9ca3+awTg7hEZVyInnMviWwxSfwmhezv3iJ0scArhJm1DIKNUT57fLPvMX2Dq9lDTBmSkXt9oZ5bG3n9YzcZkVOCUChGQLCz8RPkofo6nc79O/g+ojfFGLt8KhoyIwBqqA8aYWpBV7P4GqS8kzzQz3joV/EUBbe4KenwIDAQAB');
            
        }
        if(!defined('RSAPRIVATEKEY')) {
            //支付宝si钥
            define("RSAPRIVATEKEY",'MIIEowIBAAKCAQEAvW4V5V0Q7ofWMayhumlwbpV0BLa8f0EARtFz9YE4X3UYFnNoM2TQt1rBCaam3UnoEDXBY7L5J4ejLrpnwrXbfa7xuzqyjznpqpMSPQG593m9ToBWgaRSDU+C+cyDejsV8piNiiigkkaUTx1gvIXd/RwTiqg+zOAopAkFLpmdmyXK/sMN0V9ca3+awTg7hEZVyInnMviWwxSfwmhezv3iJ0scArhJm1DIKNUT57fLPvMX2Dq9lDTBmSkXt9oZ5bG3n9YzcZkVOCUChGQLCz8RPkofo6nc79O/g+ojfFGLt8KhoyIwBqqA8aYWpBV7P4GqS8kzzQz3joV/EUBbe4KenwIDAQABAoIBAG2neRNRoUiC51HP/bq76HKLHyLPaSQ8y10zR+3YxHo1fSEZ4zrE5DEPXukoSDWW37fqdi0xYBsq4CJfk0raHNmPWK0qGEzimEm+YvNfuXfxR81Waz0WkszTtxzE72LlKxpIhr5wMCscoiN9/Q6Ea3FmPEWqiNft9nimJBLKp7oz0m7jXS8X25zOreHxRKgSkeg10wbxfERlCPH791OS7PVtkYSxLqmK9A1UK4uythzJiJRlAKqQraEX7t6d3wN5tDPGOaWVFcPSO6efqSQVk14AChb57gWnOJ7Pr/BH3ox9wV/6JN+qGLQPcmv56PQ1LBbGXab/3WBe9iGUulQ2VYkCgYEA3Vmvm6s6o3K6w/GjyORnciYoekntY0hf1vHvNhhNDvnR7u0Q5EbV5vsfFtDMALzvcoWI4+c6+g1jTZR/m7Jro3onerjS9l9ZA1jTq/5ruq4zz1xDUfw+XVimd6ojcTnOiho4q6aW0F8Bn41yTkFtQD5YvLZWt8Mqt6Ql0ab5WHMCgYEA2xU8gBuKwTaej0OZBNTty7UPmOOIreVpJnTleDViHnn8VoJMl4mFqLBbpZhw5a+BThyqg8huXSa+e1L7msvNJkF0WuKNgklVcbSYFEG0e6xRc+UYWnaFSZ6UT9NJvpvAL965LpUHZisBVAy/0lT5KC9yQd1Su8R/D01zGaNMUiUCgYEAgLIcgA3WUiHqpPJhn/PskVnOPwuskgUKdBbGVlVauFJX1OrQ32iwBVQMh19uvR8cuSEaridLCwetrPWiHAxouOvU+G1tewGE54VFzMJoMGoIhY/HNGvMdsmMQBcT9Ej/5RvJx0NcfFpAv1umEgnIU4nsXgxF7yHyPEOcGYFTiDECgYAOfc4drzSzaRh943vIrQj5s4VPmKVe01DsQUk4dVeDKpB9bGsCjw8vRFMblgTJvJ0x/IVu6CAcu0ZYQ/numFJ+mE/I/zlbB9zGA6sXk+0LTS+qGdpNiY9xejLXQOOc3xKMWBApu6PcpzWUSK+bixFcDh9hqfEiCHJbIcVeScXvnQKBgAwjvQON9wn7IBGLbkGB4Nm9EfmgU8M1AllOG4Ko8H0tg5rvDraCOqxGjx9pGUML4bOjRoAaJ3q1epzhzM/aekeqS05DQThHe/D4JCSgsL/YvK3mvsZT2PtQwpPV62dVw0YKkmb9Wc8y+sE03ZXLptRncHpeJtNW1coEAQK7iKRr');//
            
        }  */
      }

    //支付宝支付第一步返回请求数据给客户端
    public function getClientData($data)
    {
        //接收参数
        $subject =  $data['subject'];//$data['subject'];//订单关键字
        $orderNo = $data['order_id'];//订单号out_trade_no
        $product_code = 'QUICK_MSECURITY_PAY';//销售产品码，商家和支付宝签约的产品码，为固定值
        $totalAmount = $data['total_amount'];//金额total_amount

        //处理逻辑
        $aop = new AopClient;
        $aop->gatewayUrl = GATWAYURL;
        $aop->appId = APPID;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->charset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;

        $aliPayrequest = new AlipayTradeAppPayRequest();//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $bizcontent = ["subject"=>$subject,"out_trade_no"=>$orderNo,"total_amount"=>$totalAmount,"product_code"=>$product_code];
        $bizcontent = json_encode($bizcontent);
        //$aliPayrequest->setNotifyUrl($_SERVER['SERVER_NAME']."/alipayAppcallBack");
        $aliPayrequest->setNotifyUrl("cpapi.oomall.com/alipayAppcallBack");
        //error_log(date("Y-m-d H:i:s")."回调地址-======".$aliPayrequest->setNotifyUrl("http://".$_SERVER['SERVER_NAME']."/alipayAppcallBack")."====".json_encode($aliPayrequest)."\n",3,base_path("storage/logs")."/Alipay/1aaa".date("Y-m-d").".log");
      
        $aliPayrequest->setBizContent($bizcontent);
        $dataArr = $aop->sdkExecute($aliPayrequest);
        //返回数据
        return $dataArr;
    }


    public function checkRsa($arr){
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;
        return $aop->rsaCheckV1($arr,null,SIGNTYPE);//验证签名
    }


    //获取Token并存进数据库中 用于商户Oauth2.0 验证第一次获取令牌和刷新的令牌
    public function getAppAuthToken($array){
        //处理逻辑
        $aop = new AopClient;
        $aop->gatewayUrl = GATWAYURL;
        $aop->appId = APPID;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->postCharset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;

        $request = new AlipayOpenAuthTokenAppRequest();
        $request->setBizContent(json_encode(array('grant_type'=>'authorization_code','code'=>$array['app_auth_code'])));
        $responseData = $aop->execute($request);

        if(!is_array($responseData)){
            $responseData = json_decode(json_encode($responseData), TRUE);
        }
        $data['app_auth_token'] = $responseData['alipay_open_auth_token_app_response']['app_auth_token'];
        $data['app_refresh_token'] = $responseData['alipay_open_auth_token_app_response']['app_refresh_token'];
        $data['auth_app_id'] = $responseData['alipay_open_auth_token_app_response']['auth_app_id'];
        $data['expires_in'] = $responseData['alipay_open_auth_token_app_response']['expires_in'];
        $data['re_expires_in'] = $responseData['alipay_open_auth_token_app_response']['re_expires_in'];
        $data['user_id'] = $responseData['alipay_open_auth_token_app_response']['user_id'];
        $insertId = CacheModel::getInstance()->insert($data);
        if($insertId && $responseData['alipay_open_auth_token_app_response']['code'] == 10000){
            return $data;
        }
    }

    //刷新令牌token
    public function refreshToken($arr,$userId){
        //处理逻辑
        $aop = new AopClient;
        $aop->gatewayUrl = GATWAYURL;
        $aop->appId = APPID;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->postCharset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;

        $request = new AlipayOpenAuthTokenAppRequest();
        $request->setBizContent(json_encode(array('grant_type'=>'refresh_token','refresh_token'=>$arr['refresh_token'])));
        $responseData = $aop->execute($request);

        if(!is_array($responseData)){
            $responseData = json_decode(json_encode($responseData), TRUE);
        }
        $data['app_auth_token'] = $responseData['alipay_open_auth_token_app_response']['app_auth_token'];
        $data['app_refresh_token'] = $responseData['alipay_open_auth_token_app_response']['app_refresh_token'];
        $data['auth_app_id'] = $responseData['alipay_open_auth_token_app_response']['auth_app_id'];
        $data['expires_in'] = $responseData['alipay_open_auth_token_app_response']['expires_in'];
        $data['re_expires_in'] = $responseData['alipay_open_auth_token_app_response']['re_expires_in'];
        $data['user_id'] = $responseData['alipay_open_auth_token_app_response']['user_id'];
        return CacheModel::getInstance()->where("user_id",$userId)->update($data);
    }

    //退款接口
    public function refund($refundData){
        $aop = new AopClient;
        $aop->gatewayUrl = GATWAYURL;
        $aop->appId = APPID;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->postCharset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;


        $request = new AlipayTradeRefundRequest();
        $request->setBizContent(json_encode(array('out_trade_no'=>$refundData['order_id'],'refund_amount'=>$refundData['pay_fee'])));
        $responseData = $aop->execute($request);
        Blogger::__callStatic("notice",['订单退款日志！ step 2.！ step0.', [$responseData], 'logs/alipay_refund',date('Y-m-d')]);
        $responseData = json_decode(json_encode($responseData),true);
        Blogger::__callStatic("notice",['订单退款日志！ step 3.', [$responseData], 'logs/alipay_refund',date('Y-m-d')]);
        return $responseData;
    }

    //统一收单交易撤销接口
    public function aliCcancel($orderId){
        $aop = new AopClient;
        $aop->gatewayUrl = GATWAYURL;
        $aop->appId = APPID;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->postCharset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;


        $request = new AlipayTradeCancelRequest();
        $request->setBizContent(json_encode(array('out_trade_no'=>$orderId)));
        $responseData = $aop->execute($request);
    }

    //增加支付宝查询交易状态接口
    public function tradeQuery($orderId){
        $aop = new AopClient;
        $aop->appId = APPID;
        $aop->gatewayUrl = GATWAYURL;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->postCharset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;


        $request = new AlipayTradeQueryRequest();
        $request->setBizContent(json_encode(['out_trade_no'=>$orderId]));
        $responseData = $aop->execute($request);
        Blogger::__callStatic("notice",['查询订单日志！ step 1.', [$responseData], 'logs/query',date('Y-m-d')]);
        $responseData = json_decode(json_encode($responseData),true);
        Blogger::__callStatic("notice",['查询订单日志！ step 2.', [$responseData], 'logs/query',date('Y-m-d')]);
        return $responseData['alipay_trade_query_response'];
    }

    //获取对账单下载地址功能
    public function getDownloadStatementUrl($data){
        $aop = new AopClient;
        $aop->appId = APPID;
        $aop->gatewayUrl = GATWAYURL;
        $aop->rsaPrivateKey = RSAPRIVATEKEY;
        $aop->format = FORMAT;
        $aop->postCharset = CHARSET;
        $aop->signType = SIGNTYPE;
        $aop->alipayrsaPublicKey = ALIPAYRSAPUBLICKEY;

        /**账单时间：日账单格式为yyyy-MM-dd，月账单格式为yyyy-MM。
        */
        $request = new AlipayDataDataserviceBillDownloadurlQueryRequest();
        $request->setBizContent(json_encode(['bill_date'=>$data['date'],'bill_type'=>'trade']));
        $responseData = $aop->execute($request);
        Blogger::__callStatic("notice",['支付宝下载对账单日志！ step 1.', [$responseData], 'logs/download',date('Y-m-d')]);
        $responseData = json_decode(json_encode($responseData),true);
        Blogger::__callStatic("notice",['支付宝下载对账单日志！ step 2.', [$responseData], 'logs/download',date('Y-m-d')]);
        return $responseData;
    }
}

