<?php

namespace App\Common\Library\Auth;

use App\Common\Constants\BusinessErrorCode;
use Firebase\JWT\JWT;
use RedisException;

class Authorization
{
    const SECRET_SALT = 'xxxxxxxxxxx';

    // 客户端登录配置
    const CLIENT_LOGIN_CONF = [
        'default' => [
            'expire_time' => 24 * 60 * 60, // token有效期：24小时
            'limit_multi_login' => false // 是否限制多端登录
        ],
        'admin' => [
            'expire_time' => 24 * 60 * 60,
            'limit_multi_login' => false
        ],
        'projectAdmin' => [
            'expire_time' => 24 * 60 * 60,
            'limit_multi_login' => false
        ],
        'projectManage' => [
            'expire_time' => 30 * 24 * 60 * 60,
            'limit_multi_login' => false
        ],
        'carCompany' => [
            'expire_time' => 30 * 24 * 60 * 60,
            'limit_multi_login' => false
        ],
        'Driver' => [
            'expire_time' => 30 * 24 * 60 * 60,
            'limit_multi_login' => false
        ],
        'driverWeChat' => [
            'expire_time' => 30 * 24 * 60 * 60,
            'limit_multi_login' => false
        ]
    ];

    use UserTrait;

    public static function findUser($authHeader, $checkSource = false)
    {
        if (!empty($authHeader) && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return self::findIdentityByAccessToken($matches[1], 'login', $checkSource);
        }

        return false;
    }

    public static function findIdentityByAccessToken($token, $type, $checkSource = false)
    {
        return self::getTokenJti($token, $type, $checkSource);
    }

    public static function getTokenJti($token, $type, $checkSource = false)
    {
        try {
            $decoded = JWT::decode($token, self::SECRET_SALT, [static::getAlgo()]);
        } catch (\Exception) {
            return false;
        }

        static::$decodedToken = (array)$decoded;
        if (!isset(static::$decodedToken['jti'])) {
            return false;
        }

        // 检查token来源，不可混用
        if ($checkSource !== false && $checkSource != static::$decodedToken['source']) {
            return false;
        }

        $userinfo = (string)static::$decodedToken['jti'];
        if (empty($userinfo)) {
            return false;
        }

        $userinfo = json_decode($userinfo, true);
        if (empty($userinfo)) {
            return false;
        }

        if ($type == 'login') {
            $checkResult = self::checkToken(static::$decodedToken, $userinfo, $token);
            if ($checkResult !== true) {
                return $checkResult;
            }
        }

        if ($type == 'logout') {
            self::logoutWithToken(static::$decodedToken, $userinfo, $token);
        }

        return $userinfo;
    }

    /**
     * 校验token
     * @param $token
     * @param $userinfo
     * @param $token_str
     * @return bool
     * @throws RedisException
     */
    public static function checkToken($token, $userinfo, $token_str)
    {
        $uid = $userinfo['uid'] ?? "";
        if (empty($uid)) {
            return BusinessErrorCode::NOT_LOGIN;
        }

        $loginConf = self::CLIENT_LOGIN_CONF[$token['source']] ?? self::CLIENT_LOGIN_CONF['default'];
        $redisKey = self::getRedisKey($token['source'], $uid);

        // 判断hash表是否过期
        if (!@get_redis()->exists($redisKey)) {
            return BusinessErrorCode::NOT_LOGIN;
        }

        // 获取token信息
        $thisToken = @get_redis()->hGet($redisKey, md5($token_str));
        if (!$thisToken) {
            return BusinessErrorCode::NOT_LOGIN;
        }

        // 如果已过期则删除
        $thisToken = json_decode($thisToken, true);
        if ($thisToken['expire_time'] < time()) {
            @get_redis()->hDel($redisKey, md5($token_str));

            // 清理hash中垃圾数据
            self::clearRedisHash($redisKey, $token_str, $loginConf, 'check');

            return BusinessErrorCode::NOT_LOGIN;
        }

        // 限制多端登录
        if ($thisToken['is_offline'] === true) {
            @get_redis()->hDel($redisKey, md5($token_str));

            // 清理hash中垃圾数据
            self::clearRedisHash($redisKey, $token_str, $loginConf, 'check');

            return BusinessErrorCode::MULTI_LOGIN;
        }

        // 延长token有效期
        $thisToken['expire_time'] = time() + $loginConf['expire_time'];
        @get_redis()->hSet($redisKey, md5($token_str), json_encode($thisToken));

        // 按照token有效期给hash表设置过期时间
        @get_redis()->expire($redisKey, $loginConf['expire_time']);

        return true;
    }

    /**
     * 退出登录
     * @param $authHeader
     * @param bool $checkSource
     * @return bool
     */
    public static function userLogout($authHeader, $checkSource = false)
    {
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            self::findIdentityByAccessToken($matches[1], 'logout', $checkSource);
        }

        return true;
    }

    public static function logoutWithToken($token, $userinfo, $token_str)
    {
        $uid = $userinfo['uid'] ?? "";
        if (empty($uid)) {
            return true;
        }

        $redisKey = self::getRedisKey($token['source'], $uid);

        // 判断hash表是否过期
        if (!@get_redis()->exists($redisKey)) {
            return true;
        }

        // 获取token信息
        $thisToken = @get_redis()->hGet($redisKey, md5($token_str));
        if (!$thisToken) {
            return true;
        }

        // 删除当前token
        @get_redis()->hDel($redisKey, md5($token_str));
        return true;
    }
}