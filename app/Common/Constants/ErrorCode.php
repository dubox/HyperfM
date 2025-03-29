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

use Hyperf\Constants\AbstractConstants;

/**
 * 错误码公共父类
 *  -做标识用，勿在此处定义错误码
 */

 abstract class ErrorCode extends AbstractConstants
{
    static function getMessage(int $code, $translate = null):string{
        return AbstractConstants::getMessage($code, $translate);
    }
}
