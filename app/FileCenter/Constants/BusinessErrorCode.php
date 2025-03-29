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
namespace App\FileCenter\Constants;


use App\Common\Override\Constants;

/**
 * 定义本模块独有的外部错误信息定义
 *  -错误码使用本模块专用区间范围避免与其他模块冲突
 */
#[Constants]
class BusinessErrorCode extends \App\Common\Constants\BusinessErrorCode
{
 
     /**
     * @Message("%s Test Error！")
     */
    public const TEST_ERROR = 11;
}
