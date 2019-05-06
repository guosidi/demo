<?php
/**
 * 实现支付宝和微信支付时的日志自定义功能
 * Created by PhpStorm.
 * User: ryl
 * Date: 2017/7/28
 * Time: 16:54
 */
namespace App\Tools;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;





class Blogger
{

    //define static log instance.
    protected static $_log_instance;
    /**
     * 获取log实例
     *
     * @return obj
     * @author Sphenginx
     **/
    public static function getLogInstance()
    {
        if (static::$_log_instance === null) {
            static::$_log_instance = new Logger('NOTICE');
        }
        return static::$_log_instance;
    }
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method 可用方法: debug|info|notice|warning|error|critical|alert|emergency 可调用的方法详见 Monolog\Logger 类
     * @param  array   $args 调用参数
     * demo
     * $array = [
    0=>'用户调取支付宝参数',
    1=>['url'=>"http://www.baidu.com","partner_id"=>"208796767","private_key"=>"d8de8d8ds9d8e8sd8s9d8s9d"],
    2=>'logs/Alipay',//日志所在文件夹的名字
    3=> date('Y-m-d'),//日志名称
    ];
     * @return mixed int 1
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getLogInstance();//获取
        //组织参数信息 日志描述
        $message = $args[0];
        //记录上下文日志
        $context = isset($args[1]) ? $args[1] : [];
        //定义记录日志文件
        $path  = $args[2]."/".$args[3];//isset($args[2]) ? $args[2] : "/$args[3]/"
       // echo $path; exit;
        //设置日志处理手柄，默认为写入文件（还有mail、console、db、redis等方式，详见Monolog\handler 目录）
        $handler = new StreamHandler(storage_path($path).'.log', Logger::toMonologLevel($method), $bubble = true, $filePermission = 0777);
        //设置输出格式LineFormatter(Monolog\Formatter\LineFormatter)， ignore context and extra
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $instance->setHandlers([$handler]);
        $instance->$method($message, $context);

    }
}
?>