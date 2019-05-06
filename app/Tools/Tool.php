<?php
/**
 * Created by PhpStorm.
 * User: 栾军
 * Date: 2017/11/29
 * Time: 9:48
 */

namespace App\Tools;

class Tool
{

    public function __construct()
    {

    }

    /**
     * 获取用户IP
     * @return mixed
     */
    static function getIp()
    {
        $type = 0;
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL)
            return $ip[$type];
        if (isset($_SERVER['HTTP_X_REAL_IP'])) { // nginx 代理模式下，获取客户端真实IP
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { // 客户端的ip
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // 浏览当前页面的用户计算机的网关
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR']; // 浏览当前页面的用户计算机的ip地址
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array(
            $ip,
            $long
        ) : array(
            '0.0.0.0',
            0
        );

        return $ip[$type];
    }

    /**
     * 通过IP获取城市 By 淘宝API
     * @param string $ip
     * @return bool|string
     */
    static function getCityByIP($ip = '127.0.0.1')
    {
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
        $response = file_get_contents($url);
        $info = json_decode($response);
        if ($info->code == '1') {
            return false;
        }
        $city = $info->data->city;
        return $city;
    }

    /**
     * cUrl
     * @param string $url 请求的链接
     * @param array $data 传输的数据
     * @param string $method 请求方式
     * @param array $headers 指定请求头部信息
     * @param string $cookie_file 指定cookie目录
     * @return mixed
     */
    static function callInterfaceCommon($url, $data = array(), $method = 'POST', $headers = array(), $cookie_file = "")
    {
        $ch = curl_init();
        //判断ssl连接方式
        if (stripos($url, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }

        $connttime = 300; //连接等待时间500毫秒
        $timeout = 15000;//超时时间15秒

        //如果GET请求，参数为数组，需要解析
        if (strtoupper($method) == "GET" && !empty($data)) {
            $data = http_build_query($data);
            if (strpos($url, "?")) {
                $url = $url . "&" . $data;
            } else {
                $url = $url . '?' . $data;
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url); //请求地址
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//反馈信息
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //http 1.1版本
        curl_setopt($ch,CURLOPT_NOSIGNAL,true);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connttime);//连接等待时间
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);//超时时间
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置请求头
        if ($cookie_file) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie_file);
        }

        switch (strtoupper($method)) {
            case "GET" :
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        $file_contents = curl_exec($ch);//获得返回值

        curl_close($ch);

        return $file_contents;
    }

    static function hide_phone($phone = "")
    {
        if ($phone) {
            $len = mb_strlen($phone, "UTF-8");
            if ($len == 11) {
                return substr_replace($phone, '*****', 3, 5);
            } else {
                return substr_replace($phone, '****', 3, 4);
            }
        } else {
            return $phone;
        }
    }

    static function hide_name($name = "")
    {
        if ($name) {
            $len = mb_strlen($name, "UTF-8");
            $len = $len - 1;
            $str = '';
            for ($i = 0; $i < $len; $i++) {
                $str .= "*";
            }

            $str_1 = mb_substr($name, 0, 1, 'utf-8');

            return $str_1 . $str;
        } else {
            return $name;
        }
    }
}