<?php

namespace App\Common\Library\Auth;

use \Firebase\JWT\JWT;
use RedisException;

trait UserTrait
{
    /**
     * Store JWT token header items.
     * @var array
     */
    protected static $decodedToken;

    /**
     * Getter for secret key that's used for generation of JWT
     * @return string secret key used to generate JWT
     */
    protected static function getSecretKey()
    {
        return self::SECRET_SALT;
    }

    /**
     * Getter for "header" array that's used for generation of JWT
     * @return array JWT Header Token param, see http://jwt.io/ for details
     */
    protected static function getHeaderToken()
    {
        return [];
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo()
    {
        return 'HS256';
    }

    public static function getRedisKey($source, $uid)
    {
        return 'LOGIN:TOKEN:' . strtoupper($source) . ':' . $uid;
    }

    public static function clearRedisHash($redisKey, $token, $loginConf, $type)
    {
        // 获取用户当前所有登录token
        $userTokenList = @get_redis()->hGetAll($redisKey);
        foreach ($userTokenList as $key => $value) {
            $thisToken = json_decode($value, true);
            if ($key != md5($token)) {
                // 如果已过期则删除
                if ($thisToken['expire_time'] < time()) {
                    @get_redis()->hDel($redisKey, $key);
                    continue;
                }

                // 判断是否限制多端登录
                if ($type == 'login') {
                    if ($loginConf['limit_multi_login']) {
                        $thisToken['is_offline'] = true;
                        @get_redis()->hSet($redisKey, $key, json_encode($thisToken));
                    }
                }
            }
        }
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @param $source
     * @param $token_info
     * @return string encoded JWT
     * @throws RedisException
     */
    public static function getJWT($source, $token_info)
    {
        // Collect all the data
        $secret = static::getSecretKey();
        $currentTime = time();
        $hostInfo = @request()->getUri()->getHost() ?? "";

        $loginConf = self::CLIENT_LOGIN_CONF[$source] ?? self::CLIENT_LOGIN_CONF['default'];

        // Merge token with presets not to miss any params in custom
        $token = array_merge([
            'iss' => $hostInfo,
            'aud' => $hostInfo,
            'iat' => $currentTime,
            'nbf' => $currentTime
        ], static::getHeaderToken());

        // Set up id
        $token['jti'] = json_encode($token_info);
        $token['source'] = $source;
        $jwtToken = JWT::encode($token, $secret, static::getAlgo());

        // 将用户token存储到redis hash表
        $redisKey = self::getRedisKey($token['source'], $token_info['uid']);
        $redisValue = ['expire_time' => time() + $loginConf['expire_time'], 'is_offline' => false];
        @get_redis()->hSet($redisKey, md5($jwtToken), json_encode($redisValue));

        // 清理hash中垃圾数据
        self::clearRedisHash($redisKey, $jwtToken, $loginConf, 'login');

        // 按照token有效期给hash表设置过期时间
        @get_redis()->expire($redisKey, $loginConf['expire_time']);

        return $jwtToken;
    }
}