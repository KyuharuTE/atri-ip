<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Utils.php';

class Auth
{
    private static function getUser($token)
    {
        if (empty($token)) {
            return null;
        }

        $db = Database::getInstance();
        $token = $db->escape($token);
        $users = $db->selectData("users", "token = '$token'", "*", 1);

        return empty($users) ? null : $users[0];
    }

    public static function check($token)
    {
        $user = self::getUser($token);
        if (!$user) {
            Response::unauthorized('用户不存在或Token无效');
        }
        return $user;
    }

    public static function register($email, $username, $password)
    {
        if (!Utils::validateEmail($email)) {
            Response::error('邮箱格式错误');
        }

        if (!Utils::validateUsername($username)) {
            Response::error('用户名只能包含字母、数字、下划线，且长度为3-16');
        }

        if (!Utils::validatePassword($password)) {
            Response::error('密码长度至少为6，最大为16，且只能是字母数字下划线');
        }

        $db = Database::getInstance();

        if (!empty($db->selectData("users", "email = '" . $db->escape($email) . "'"))) {
            Response::error('该邮箱已被注册');
        }

        $token = Utils::generateToken();
        $userData = [
            'email' => $email,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'token' => $token,
            'created_at' => time()
        ];

        if (!$db->insertData('users', $userData)) {
            Response::serverError('用户注册失败');
        }

        $user = $db->selectData("users", "email = '" . $db->escape($email) . "'")[0];
        unset($user['password']);

        return $user;
    }

    public static function login($email, $password)
    {
        if (!Utils::validateEmail($email)) {
            Response::error('邮箱格式错误');
        }

        if (!Utils::validatePassword($password)) {
            Response::error('密码格式错误');
        }

        $db = Database::getInstance();
        $users = $db->selectData("users", "email = '" . $db->escape($email) . "'");

        if (empty($users) || !password_verify($password, $users[0]['password'])) {
            Response::error('邮箱或密码错误');
        }

        $token = Utils::generateToken();
        $db->updateData("users", ['token' => $token], "email = '" . $db->escape($email) . "'");

        $userData = $users[0];
        unset($userData['password']);
        $userData['token'] = $token;

        return $userData;
    }
}
