<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Response.php';

$token = $_GET['token'] ?? '';

try {
    // 验证用户并获取信息
    $user = Auth::check($token);

    // 移除敏感信息
    unset($user['password']);

    Response::success($user);
} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
