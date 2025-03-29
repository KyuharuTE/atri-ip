<?php
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../services/IpzService.php';
require_once __DIR__ . '/../Auth.php';

$token = $_GET['token'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (empty($token)) {
    Response::error('Token不能为空');
}

if (empty($id)) {
    Response::error('ID不能为空');
}

try {
    $user = Auth::check($token);

    $ipzService = IpzService::getInstance();
    $ipzService->deleteIpz($id, $user['email']);

    Response::success(null, '删除成功');
} catch (Exception $e) {
    Response::error($e->getMessage());
}
