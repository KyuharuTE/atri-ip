<?php
require_once __DIR__ . '/config.php';

class Database
{
    private static $instance = null;
    private $conn;
    private $connected = false;

    private function __construct()
    {
        $this->connect();
    }

    private function __clone() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        if (!$this->connected) {
            $this->conn = new mysqli(
                config::DB_HOST,
                config::DB_USER,
                config::DB_PASS,
                config::DB_NAME
            );

            if ($this->conn->connect_error) {
                throw new Exception("数据库连接失败: " . $this->conn->connect_error);
            }

            $this->conn->set_charset('utf8mb4');
            $this->connected = true;
        }
    }

    public function insertData($table, $data)
    {
        $keys = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_map([$this, 'escape'], array_values($data))) . "'";
        $sql = "INSERT INTO $table ($keys) VALUES ($values)";

        return $this->query($sql);
    }

    public function selectData($table, $condition = "", $fields = "*", $limit = null)
    {
        $sql = "SELECT $fields FROM $table";
        if (!empty($condition)) {
            $sql .= " WHERE $condition";
        }
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
        }

        $result = $this->query($sql);
        if (!$result) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $result->free();
        return $rows;
    }

    public function updateData($table, $data, $condition)
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = '" . $this->escape($value) . "'";
        }
        $setClause = implode(", ", $set);
        $sql = "UPDATE $table SET $setClause WHERE $condition";

        return $this->query($sql);
    }

    public function deleteData($table, $condition)
    {
        $sql = "DELETE FROM $table WHERE $condition";
        return $this->query($sql);
    }

    public function escape($data)
    {
        return $this->conn->real_escape_string($data);
    }

    public function getLastInsertId()
    {
        return $this->conn->insert_id;
    }

    private function query($sql)
    {
        try {
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new Exception("查询执行失败: " . $this->conn->error);
            }
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function beginTransaction()
    {
        return $this->conn->begin_transaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollback();
    }

    public function close()
    {
        if ($this->connected) {
            $this->conn->close();
            $this->connected = false;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
