<?php
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../services/IpzService.php';
require_once __DIR__ . '/../Auth.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    Response::error('Token不能为空');
}

try {
    $user = Auth::check($token);

    $ipzService = IpzService::getInstance();
    $ipzService->addIpz($user['email']);

    Response::success(null, '添加成功');
} catch (Exception $e) {
    Response::error($e->getMessage());
}
