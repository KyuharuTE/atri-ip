<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../config.php';

class IpzService
{
    private static $instance = null;
    private $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function __clone() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getUserIpzs($email)
    {
        return $this->db->selectData("ipzs", "email = '" . $this->db->escape($email) . "'");
    }

    public function getIpzById($id)
    {
        $result = $this->db->selectData("ipzs", "id = " . intval($id));
        return empty($result) ? null : $result[0];
    }

    public function getIpsByIpzId($id)
    {
        return $this->db->selectData("ip", "owner = " . intval($id));
    }

    public function addIpz($email)
    {
        // 获取用户当前的IP探针数量
        $user = $this->db->selectData("users", "email = '" . $this->db->escape($email) . "'")[0];

        if ($user['ip_count'] >= config::MAX_IP_COUNT) {
            throw new Exception('IP探针已达上限');
        }

        $this->db->beginTransaction();
        try {
            if (!$this->db->insertData("ipzs", ['email' => $email])) {
                throw new Exception('添加探针失败');
            }

            if (!$this->db->updateData(
                "users",
                ['ip_count' => $user['ip_count'] + 1],
                "email = '" . $this->db->escape($email) . "'"
            )) {
                throw new Exception('更新用户探针数量失败');
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function deleteIpz($id, $email)
    {
        $this->db->beginTransaction();
        try {
            // 删除探针
            if (!$this->db->deleteData("ipzs", "id = " . intval($id) . " AND email = '" . $this->db->escape($email) . "'")) {
                throw new Exception('删除探针失败');
            }

            // 删除相关的IP记录
            $this->db->deleteData("ips", "owner = " . intval($id));

            // 更新用户的探针数量
            $user = $this->db->selectData("users", "email = '" . $this->db->escape($email) . "'")[0];
            if (!$this->db->updateData(
                "users",
                ['ip_count' => $user['ip_count'] - 1],
                "email = '" . $this->db->escape($email) . "'"
            )) {
                throw new Exception('更新用户探针数量失败');
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
