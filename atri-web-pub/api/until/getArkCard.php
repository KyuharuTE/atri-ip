<?php
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../services/IpzService.php';
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? '';
$token = $_GET['token'] ?? '';
$description = $_GET['description'] ?? '';

if (empty($token)) {
    Response::error('Token不能为空');
}

if (empty($id)) {
    Response::error('ID不能为空');
}

if (empty($description)) {
    Response::error('描述不能为空');
}

try {
    // 验证用户
    Auth::check($token);

    // 验证探针是否存在
    $ipzService = IpzService::getInstance();
    if (!$ipzService->getIpzById($id)) {
        Response::notFound('探针不存在');
    }

    $domain = $_SERVER['SERVER_NAME'];
    $body = [
        "title" => $description,
        "desc" => $description,
        "url" => "https://$domain/get.php?id=$id"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, config::ARK_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $ark = curl_exec($ch);
    curl_close($ch);

    if ($ark === false) {
        throw new Exception('请求 ark 接口失败');
    }

    $response = json_decode($ark, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('解析 ark 响应失败');
    }

    Response::success($response['message']);
} catch (Exception $e) {
    Response::error($e->getMessage());
}
