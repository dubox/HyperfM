<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Common\Constants;

use App\Common\Override\Constants;

/**
 * 外部错误信息定义（即，面向用户的错误信息）
 * 10000 < code < 99999
 */
#[Constants]
class BusinessErrorCode extends ErrorCode
{
    /**
     * @Message("成功")
     */
    public const SUCCESS = 10000;

    /**
     * @Message("未登录或登录已过期")
     */
    public const NOT_LOGIN = 10001;

    /**
     * @Message("账号在其它设备登录")
     */
    public const MULTI_LOGIN = 10011;

    /**
     * @Message("%s参数错误")
     */
    public const PARAM_ERROR = 10002;

    /**
     * @Message("%s数据不存在")
     */
    public const DATA_LACK = 10003;

    /**
     * @Message("权限不足")
     */
    public const NO_AUTH = 10004;

    /**
     * @Message("请求方式错误")
     */
    public const METHOD_NOT_ALLOWED = 10005;

    /**
     * @Message("%s已被禁用")
     */
    public const HAS_BEEN_DISABLED = 10006;

    /**
     * @Message("%s")
     */
    public const CUSTOM_ERROR = 10007;

    /**
     * @Message("%s未找到")
     */
    public const NOT_FOUND = 10022;

    /**
     * @Message("%s不存在")
     */
    public const NOT_EXISTS = 10023;

    /**
     * @Message("系统内部错误")
     */
    public const SERVER_ERROR = 99999;


    /**
     * @Message("操作失败，请重试")
     */
    public const COMMON_ERROR = 10030;
    
}
