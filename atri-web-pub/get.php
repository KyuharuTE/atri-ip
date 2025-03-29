<?php
require_once __DIR__ . '/api/Response.php';
require_once __DIR__ . '/api/Database.php';
require_once __DIR__ . '/api/Utils.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    Response::error('缺少参数');
}

$ip = Utils::getClientIp();

if (empty($ip)) {
    Response::error('无效的IP地址');
}

if ($ip == "183.60.2.55" || $ip == "9.148.116.94") {
    exit;
}

try {
    $db = Database::getInstance();

    if (empty($db->selectData("ip", "ip = '$ip' AND owner = '$id'"))) {
        $db->beginTransaction();
        try {
            // 插入IP记录
            if (!$db->insertData("ip", [
                'ip' => $ip,
                'owner' => $id
            ])) {
                throw new Exception('插入IP记录失败');
            }

            // 更新探针计数
            $ipz = $db->selectData("ipzs", "id = '$id'");
            if (empty($ipz)) {
                throw new Exception('探针不存在');
            }

            if (!$db->updateData("ipzs", [
                'count' => $ipz[0]['count'] + 1
            ], "id = '$id'")) {
                throw new Exception('更新探针计数失败');
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
