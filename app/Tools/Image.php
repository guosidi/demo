<?php
/**
 * Created by PhpStorm.
 * User: 栾军
 * Date: 2018/3/7
 * Time: 15:28
 */

namespace App\Tools;

class Image
{
    public static function upImgFile($file, $width = "", $height = "")
    {
        $source = new \CURLFile($file['tmp_name'], $file['type'], $file['name']);
        $ch = curl_init();
        if (class_exists('\CURLFile')) {// 这里用特性检测判断php版本
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
            $data = array('upFile' => $source);//>=5.5
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
            $data = array('upFile' => '@' . realpath($source));//<=5.5
        }
        if (!empty($width)) {
            $data['width'] = $width;
        }
        if (!empty($height)) {
            $data['height'] = $height;
        }
        curl_setopt($ch, CURLOPT_URL, config("constants.UPIMGURL", 'http://file.oomall.com/api/upload'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, "TEST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}