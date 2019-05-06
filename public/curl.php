<?php

$url = "http://testcpapi.oomall.com/alipay";
$url = "http://testcpapi.oomall.com/sendupdateremain";

$post_data2['out_trade_no']='115378595048094';
$post_data2['subject']='订单';
$post_data2['total_amount']='70';

//$post_data2= json_decode('{"canteen_id":"1485","data_type":"1","sign":"68E5A074FD94D5ACDC4DDC3BE40D23E0","user_id":"1005"}',true);

$post_data2['sign']='3AEACEF306868BAE82F9A70D4370EB97';
if (isset($_REQUEST['debug']))
    $url = $url . "?debug=" . intval($_REQUEST['debug']);
    /*
     *
     * $header[] = "meid: 865546036183321";
     * $header[] = "mobile-type: Android";
     * $header[] = "app-version:1.0.6";
     * $header[] = "app-version_no: 1.0.6";
     * $header[] = "os-version: 7.0_24";
     * $header[] = "mobile-number: ";
     * $header[] = "mobile-model: BLN-AL40";
     * $header[] = "app-channel_id: ";
     * $header[] = "app-type: 1";
     */
    
    $header[] = "meid:863454038551235";
    $header[] = "mobile-type: Android";
    $header[] = "app-version: 1.0.8";
    $header[] = "app-version_no: 1.0";
    $header[] = "os-version: 6.0_23";
    $header[] = "mobile-number: 18610865085";
    $header[] = "mobile-model: mobile-model: vivo X7";
    $header[] = "app-channel_id: ";
    $header[] = "app-type: 1";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data2);
    $output = curl_exec($ch);
    curl_close($ch);
    // echo $output;
    print_r($output);