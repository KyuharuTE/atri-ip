<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../services/UserService.php';

class MessageService
{
    private static $instance = null;
    private $db;
    private $userService;

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->userService = UserService::getInstance();
    }

    private function __clone() {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getLongMsgFromApi($raw_pb)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config::RESID_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_pb);
        $resid = curl_exec($ch);
        curl_close($ch);

        if ($resid === false) {
            throw new Exception('请求resid接口失败');
        }

        $response = json_decode($resid, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('解析resid响应失败');
        }

        return [
            "37" => [
                "6" => 1,
                "7" => $response['message'],
                "17" => 0,
                "19" => [
                    "15" => 0,
                    "31" => 0,
                    "41" => 0
                ]
            ]
        ];
    }

    public function processLongMsg($token, $id, $raw_pb)
    {
        // 验证token
        $user = $this->db->selectData("users", "token = '" . $this->db->escape($token) . "'");
        if (empty($user)) {
            throw new Exception('token不存在');
        }

        if ($id == "-52091") {
            // 特殊ID处理
            $response = file_get_contents("https://" . $_SERVER['SERVER_NAME'] . "/api/until/getPrices.php");
            if ($response === false) {
                throw new Exception('获取价格信息失败');
            }

            $priceInfo = json_decode($response, true);
            $price = $priceInfo["data"]["long_msg"];

            // 扣除用户积分
            $this->userService->updateUserCoin($token, -$price);
        } else {
            // 普通ID处理
            if (empty($this->db->selectData("ipzs", "id = " . intval($id)))) {
                throw new Exception('id不存在');
            }

            if (strpos($raw_pb, 'ip.afrit.cn') === false) {
                throw new Exception('raw_pb不合法');
            }
        }

        return $this->getLongMsgFromApi($raw_pb);
    }
}
