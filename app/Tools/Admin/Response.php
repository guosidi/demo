<?php
/**
 * Created by PhpStorm.
 * User: 栾军
 * Date: 2018/5/24
 * Time: 18:37
 */

namespace App\Tools\Admin;

class Response
{
    /**
     * 通用成功提示
     * @param string $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    static function success($message = "操作成功", $data = [])
    {
        if (!$data) {
            if (is_array($message) || is_object($message)) {
                $data = $message;
                $message = "success";
            }
        }

        $result = [
            'code' => 200,
            'message' => $message
        ];

        if ($data) {
            $result["data"] = $data;
        }
        return response()->json($result);
    }

    /**
     * 通用错误提示
     * @param string $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    static function error($message = "操作失败", $data = [])
    {
        $result = [
            'code' => 422,
            'message' => $message
        ];
        if ($data) {
            $result["data"] = $data;
        }
        return response()->json($result);
    }

    /**
     * 自定义信息提示
     * @param string $message
     * @param int $code
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    static function alert($message = "message", $code = 200, $data = [])
    {
        $result = [
            'code' => $code,
            'message' => $message
        ];

        if ($data) {
            $result["data"] = $data;
        }

        return response()->json($result);
    }
}