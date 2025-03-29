<?php

namespace App\Common\Library\Wechat;


use App\Common\Exception\BusinessException;
use App\Common\Library\Request\Http;
use App\Common\Constants\BusinessErrorCode;
use Exception;
use RedisException;

/**
 * 微信小程序
 */
class Miniapp
{
    public array $config = [];

    public function __construct($config = [])
    {
        
        $appId = empty($config) ? m_config('wechat.appId') : $config['appId'];
        $secret = empty($config) ? m_config('wechat.secret') : $config['secret'];
        if (empty($appId) || empty($secret)) {
            throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, '微信配置错误');
        }

        $this->config['appId'] = $appId;
        $this->config['secret'] = $secret;
    }

    /**
     * 获取access_token
     * @param bool $isRefresh
     * @return string
     * @throws RedisException
     */
    public function getAccessToken(bool $isRefresh = false): string
    {
        $redis_cache_key = 'wechat:AccessToken:' . $this->config['appId'];

        // 判断是否存在缓存
        if (!$isRefresh && get_redis()->exists($redis_cache_key)) {
            return get_redis()->get($redis_cache_key);
        }

        $access_token = '';

        $params = [
            'grant_type' => 'client_credential',
            'appid' => $this->config['appId'],
            'secret' => $this->config['secret'],
        ];
        $response = Http::get('WECHAT_OPEN_URL', '/cgi-bin/token?' . http_build_query($params));
        if (!empty($response) && $response['code'] == 200) {
            $access_token = $response['data']->access_token ?? "";
        }

        // 设置缓存
        !empty($access_token) && get_redis()->setex($redis_cache_key, 60 * 60, $access_token);
        return $access_token;
    }

    /**
     * 获取用户openId
     * @param $code
     * @return string
     */
    public function getOpenId($code): string
    {
        $openid = '';

        // 小程序登录
        $params = [
            'appid' => $this->config['appId'],
            'secret' => $this->config['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];

        try {
            $response = Http::get('WECHAT_OPEN_URL', '/sns/jscode2session?' . http_build_query($params));
        } catch (Exception) {
            $response = [];
        }
        if (!empty($response) && $response['code'] == 200) {
            $openid = $response['data']->openid ?? "";
        }

        return $openid;
    }

    /**
     * 获取用户手机号
     * @param $code
     * @param bool $isRefresh
     * @return string
     * @throws RedisException
     */
    public function getPhoneNumber($code, bool $isRefresh = false): string
    {
        $phoneNumber = '';

        $access_token = $this->getAccessToken($isRefresh);

        try {
            $response = Http::post('WECHAT_OPEN_URL', '/wxa/business/getuserphonenumber?access_token=' . $access_token, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => ['code' => $code]
            ]);
        } catch (Exception) {
            $response = [];
        }

        if (!empty($response) && $response['code'] == 200) {
            // 如果是access_token已被其他环境获取最新，导致当前缓存的失效，进行刷新处理
            // 避免死循环，只处理一次
            if (!$isRefresh && $response['data']->errcode == 40001 && str_contains($response['data']->errmsg, 'not latest')) {
                return $this->getPhoneNumber($code, true);
            }

            $phoneNumber = $response['data']->phone_info->purePhoneNumber ?? "";
        }

        return $phoneNumber;
    }
}
