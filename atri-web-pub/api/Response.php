<?php
require_once __DIR__ . '/config.php';

class Response
{
    public static function json($data = null, $code = config::CODE_SUCCESS, $message = '')
    {
        header('Content-Type: application/json');
        $response = [
            'code' => $code
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message) {
            $response['message'] = $message;
        }

        echo json_encode($response);
        exit;
    }

    public static function success($data = null, $message = '')
    {
        self::json($data, config::CODE_SUCCESS, $message);
    }

    public static function error($message, $code = config::CODE_BAD_REQUEST)
    {
        self::json(null, $code, $message);
    }

    public static function unauthorized($message = '未授权访问')
    {
        self::json(null, config::CODE_UNAUTHORIZED, $message);
    }

    public static function notFound($message = '资源不存在')
    {
        self::json(null, config::CODE_NOT_FOUND, $message);
    }

    public static function serverError($message = '服务器内部错误')
    {
        self::json(null, config::CODE_SERVER_ERROR, $message);
    }
}
