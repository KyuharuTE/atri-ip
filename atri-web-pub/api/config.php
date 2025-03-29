<?php
class config
{
    // HTTP 状态码
    const CODE_SUCCESS = 200;
    const CODE_BAD_REQUEST = 400;
    const CODE_UNAUTHORIZED = 401;
    const CODE_NOT_FOUND = 404;
    const CODE_RATE_LIMIT = 429;
    const CODE_SERVER_ERROR = 500;

    // 签到配置
    const SIGN_COIN = 1;

    // 用户名配置
    const USERNAME_MIN_LENGTH = 3;
    const USERNAME_MAX_LENGTH = 16;

    // 密码配置
    const PASSWORD_MIN_LENGTH = 6;
    const PASSWORD_MAX_LENGTH = 16;

    // IP 黑名单
    const IP_BLACKLIST = [];

    // 速率限制配置
    const RATE_LIMIT_MAX_REQUESTS = 60;  // 每个时间窗口内的最大请求数
    const RATE_LIMIT_WINDOW = 60;        // 时间窗口大小（秒）
    const RATE_LIMIT_PATH = __DIR__ . '/user/rate_limit';  // 速率限制数据存储路径

    // 数据库配置
    const DB_HOST = 'localhost';
    const DB_PORT = 3306;
    const DB_USER = '';
    const DB_PASS = '';
    const DB_NAME = '';

    // IP 池配置
    const MAX_IP_COUNT = 20;

    // API 配置
    const RESID_API_URL = "";
    const ARK_API_URL = "";
}
