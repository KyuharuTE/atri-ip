<?php
require_once __DIR__ . '/config.php';

class Utils
{
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateUsername($username)
    {
        return preg_match('/^[a-zA-Z0-9_]{' . config::USERNAME_MIN_LENGTH . ',' . config::USERNAME_MAX_LENGTH . '}$/', $username);
    }

    public static function validatePassword($password)
    {
        return preg_match('/^[a-zA-Z0-9_]{' . config::PASSWORD_MIN_LENGTH . ',' . config::PASSWORD_MAX_LENGTH . '}$/', $password);
    }

    public static function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '';
    }

    public static function generateToken()
    {
        return time() . bin2hex(random_bytes(16));
    }

    public static function isIpBlacklisted($ip)
    {
        return in_array($ip, Config::IP_BLACKLIST);
    }

    public static function rateLimit($ip, $limit = null, $window = null)
    {
        $limit = $limit ?? Config::RATE_LIMIT_MAX_REQUESTS;
        $window = $window ?? Config::RATE_LIMIT_WINDOW;
        $rateLimitDir = Config::RATE_LIMIT_PATH;

        if (!file_exists($rateLimitDir)) {
            if (!mkdir($rateLimitDir, 0755, true)) {
                return false;
            }
        }

        $filePath = $rateLimitDir . '/' . md5($ip) . '.json';
        $file = fopen($filePath, 'c+');
        if (!$file) {
            return false;
        }

        flock($file, LOCK_EX);

        $data = [];
        if (filesize($filePath) > 0) {
            $content = fread($file, filesize($filePath));
            $data = json_decode($content, true) ?? [];
        }

        $currentTime = time();
        $firstRequestTime = $data['first_request_time'] ?? $currentTime;
        $count = $data['count'] ?? 0;

        if ($currentTime - $firstRequestTime <= $window) {
            if ($count >= $limit) {
                flock($file, LOCK_UN);
                fclose($file);
                return false;
            }
            $count++;
        } else {
            $firstRequestTime = $currentTime;
            $count = 1;
        }

        ftruncate($file, 0);
        rewind($file);
        fwrite($file, json_encode([
            'first_request_time' => $firstRequestTime,
            'count' => $count
        ]));

        flock($file, LOCK_UN);
        fclose($file);

        return true;
    }
}
