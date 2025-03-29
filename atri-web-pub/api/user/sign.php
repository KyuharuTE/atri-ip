<?php
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../services/UserService.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    Response::error('Token不能为空');
}

try {
    $userService = UserService::getInstance();
    $result = $userService->signIn($token);
    Response::success($result);
} catch (Exception $e) {
    Response::error($e->getMessage());
}
