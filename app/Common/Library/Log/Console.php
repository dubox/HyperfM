<?php

namespace App\Common\Library\Log;

use App\Common\Exception\BusinessException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Validation\ValidationException;
use Throwable;


/**
 * 控制台输出
 */
class Console
{

    protected static function getLogger():StdoutLoggerInterface{
        return container(StdoutLoggerInterface::class);
    }
    
    static function error(Throwable $throwable){
        $errorMsg = sprintf("%s in:\n%s(%s)", $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
        $logger = static::getLogger();
        $logger->error($errorMsg);
        $logger->error($throwable->getTraceAsString());
        // 记录错误日志
        if (
            !match_class($throwable,BusinessException::class)
            && !match_class($throwable,ValidationException::class)
            ) {
            Log::error($errorMsg);
        }
    }

}