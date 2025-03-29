<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../config.php';

// 速率限制检查
$clientIp = Utils::getClientIp();
if (!Utils::rateLimit($clientIp)) {
    Response::error('请求过于频繁，请稍后再试', config::CODE_RATE_LIMIT);
}

// 获取并验证输入
$input = json_decode(file_get_contents('php://input'));
if (!$input) {
    Response::error('无效的请求数据');
}

$email = $input->email ?? '';
$username = $input->username ?? '';
$password = $input->password ?? '';

if (empty($email) || empty($username) || empty($password)) {
    Response::error('邮箱、用户名、密码不能为空');
}

try {
    // 注册用户
    $userData = Auth::register($email, $username, $password);
    Response::success($userData);
} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
