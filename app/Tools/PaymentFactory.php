<?php
/**支付接口的工厂类，对应不同的支付网关
 * 2017-08-14
 * Author ryl
*/
namespace App\Tools;

class PaymentFactory{
    public static function factory($payType){
    $payTypeName = ucfirst(strtolower($payType));
        $payDriver = "\\App\\PayLibaries\\$payTypeName";
        return new $payDriver();
}
}

?>