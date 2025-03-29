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
 * 内部错误信息定义
 * 9999 > code > 1000
 * 
 * @method static string getMessage(int $code,array $translate = null)
 */
#[Constants]
class InternalErrorCode extends ErrorCode
{
   

    /**
     * @Message("Child class not found")
     */
    public const CHILD_NOT_FOUND = 1101;

    /**
     * @Message("TRANSACTION fail")
     */
    public const TRANSACTION_FAIL = 1102;

    /**
     * @Message("condition fail")
     */
    public const CONDITION_FAIL = 1103;

    /**
     * @Message("need implement")
     */
    public const NEED_IMPLEMENT = 1104;


    /**
     * @Message("Class not found : %s")
     */
    public const CLASS_NOT_FOUND = 1105;


    /**
     * @Message("Service autoload failed : %s")
     */
    public const SERVICE_AUTOLOAD_FAILED = 1106;


    /**
     * @Message("File not found")
     */
    public const FILE_NOT_FOUND = 1107;

    /**
     * @Message("%s")
     */
    public const CUSTOM_ERR = 1200;
}
