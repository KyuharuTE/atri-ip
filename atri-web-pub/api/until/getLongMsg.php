<?php
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../services/MessageService.php';

$id = $_GET['id'] ?? '';
$raw_pb = $_GET['raw_pb'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    Response::error('Token不能为空');
}

try {
    $messageService = MessageService::getInstance();
    $result = $messageService->processLongMsg($token, $id, $raw_pb);
    Response::success($result);
} catch (Exception $e) {
    Response::error($e->getMessage());
}
