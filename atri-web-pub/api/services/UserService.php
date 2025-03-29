<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Response.php';
require_once __DIR__ . '/../config.php';

class UserService
{
    private static $instance = null;
    private $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 用户签到
     * @param string $token 用户token
     * @return array 签到结果
     */
    public function signIn($token)
    {
        // 验证用户
        $user = Auth::check($token);

        // 获取今天的开始时间戳
        $todayStart = strtotime(date('Y-m-d'));

        // 检查今天是否已经签到
        $signRecord = $this->db->selectData(
            "users",
            "token = '{$user['token']}' AND last_sign_time >= $todayStart",
            "*",
            1
        );

        if (!empty($signRecord)) {
            Response::error('今天已经签到过了');
        }

        // 更新用户信息
        $userData = [
            'coin' => $user['coin'] + config::SIGN_COIN,
            'last_sign_time' => $todayStart
        ];

        $this->db->updateData("users", $userData, "token = '{$user['token']}'");

        return [
            'coin' => config::SIGN_COIN,
            'message' => '签到成功'
        ];
    }

    public function updateUserCoin($token, $coin)
    {
        $user = Auth::check($token);

        $userData = [
            'coin' => $user['coin'] + $coin
        ];
        if (!$this->db->updateData("users", $userData, "token = '{$user['token']}'")) {
            Response::serverError('更新失败');
        }
        
        return [
            'coin' => $coin,
            'message' => '更新金币成功'
        ];
    }
}
