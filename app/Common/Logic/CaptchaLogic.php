<?php

declare(strict_types=1);

namespace App\Common\Logic;

use App\Common\Constants\BusinessErrorCode;
use App\Common\Message\SmsPhoneCode;
use App\Common\Exception\BusinessException;
use App\Common\Library\Log\Log;
use App\Common\Library\Message\Sender;
use App\Common\Logic\AbstractLogic;

class CaptchaLogic extends AbstractLogic
{


    static function prefix(): string
    {
        $class = get_called_class();
        if (!$class) return '';
        preg_match_all('/^\\\{0,1}App\\\([^\\\]*)\\\.*$/', $class, $match, PREG_PATTERN_ORDER);
        return $match[1][0] ?? '';
    }

    /**
     * 发送验证码
     * @param $mobile
     * @return boolean
     */
    public static function send(string $type, $mobile)
    {
        if (empty($mobile) || !preg_match("/^1[345789]\d{9}$/", $mobile)) {
            throw new BusinessException(BusinessErrorCode::PARAM_ERROR, '手机号');
        }

        $redis_lock_key = static::prefix() . ':' . $type . ':captcha:lock:' . $mobile;
        $redis_cache_key = static::prefix() . ':' . $type . ':captcha:cache:' . $mobile;
        $config = m_config('captcha');

        // 增加redis锁，防止频繁发送
        if (!get_redis_lock($redis_lock_key, $config['lockTime'] ?? 600)) {
            throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "操作过于频繁，请稍后再试");
        }

        $sendStatus = intval(env('SMS_SEND_STATUS', 0));

        // 如果有未过期验证码
        $code = get_redis()->get($redis_cache_key);
        if (empty($code) || !$sendStatus) {
            $code = get_random_captcha(len: 6, status: (bool)$sendStatus);
        }

        // 发送短信
        if ($sendStatus) {
            $result = Sender::send(new SmsPhoneCode($mobile, $code))[0];
            if ($result->error) {
                // 清空redis锁
                del_redis_lock($redis_lock_key);
                Log::error($result->error);
                throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "验证码发送失败，请稍后再试");
            }
        }

        // 设置缓存
        get_redis()->setex($redis_cache_key, $config['cacheTime'] ?? 600, $code);
        return true;
    }

    /**
     * 检查验证码
     *
     * @param string $type
     * @param [type] $mobile
     * @param [type] $captcha
     * @return boolean
     */
    public static function check(string $type, $mobile, $captcha): bool
    {
        // 测试账号登录
        if ($type == 'login' && $mobile == env('TEST_ACCOUNT_PHONE', '13000000000')) {
            if (env('TEST_ACCOUNT_CODE', '123456') != $captcha) {
                throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "短信验证码错误或已过期，请重新提交");
            }
        } else {
            $redis_cache_key = static::prefix() . ':' . $type . ':captcha:cache:' . $mobile;

            if (get_redis()->get($redis_cache_key) != $captcha) {
                throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "短信验证码错误或已过期，请重新提交");
            }

            get_redis()->setex($redis_cache_key, m_config('captcha.cacheTime') ?? 600, "1");
        }
        
        return true;
    }


    public static function justChecked(string $type, $mobile)
    {
        $redis_cache_key = static::prefix() . ':' . $type . ':captcha:cache:' . $mobile;
        if (get_redis()->get($redis_cache_key) != 1) {
            throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "操作超时，请返回上一步重新操作");
        }
        return true;
    }

}
