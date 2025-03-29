<?php
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../services/IpzService.php';

$id = intval($_GET['id'] ?? 0);

if (empty($id)) {
    Response::error('id不能为空');
}

try {
    $ipzService = IpzService::getInstance();

    $ipz = $ipzService->getIpzById($id);
    if (!$ipz) {
        Response::notFound('探针不存在');
    }

    $ips = $ipzService->getIpsByIpzId($id);

    Response::success($ips);
} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
