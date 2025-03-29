<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../services/IpzService.php';

$token = $_GET['token'] ?? '';

try {
    $user = Auth::check($token);

    $ipzService = IpzService::getInstance();
    $ipzs = $ipzService->getUserIpzs($user['email']);

    Response::success($ipzs);
} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
