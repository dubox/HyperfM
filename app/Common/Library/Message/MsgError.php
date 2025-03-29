<?php

declare(strict_types=1);

namespace App\Common\Library\Message;
use Throwable;


enum MsgError:string
{
    
    /**
     * 网络请求错误，即远程服务没有按照约定返回正常的成功或失败信息
     */
    case NET_ERR = '网络错误';

    /**
     * 本地代码异常
     */
    case CLIENT_ERR = '客户端错误';
    
    /**
     * 远程服务正常返回错误信息
     */
    case API_ERR = '业务错误';
    
    /**
     * 其他无法归类到以上的错误
     */
    case OTHER_ERR = '其他错误';
    
    /**
     * 其他无法归类到以上的错误
     */
    case NO_RECEIVER = '无收件人';




    public function code():int{
        return match ($this) {
            self::NET_ERR => 500,
            self::CLIENT_ERR => 501,
            self::API_ERR => 502,
            self::NO_RECEIVER => 503,
            self::OTHER_ERR => 509,
        };
    }


    public function exception(MsgType $msgType, string $message = null, Throwable $previous = null):MsgException{
        return new MsgException(
            $msgType,
            $this,
            $this->code(),
            $message?:$this->value,
            $previous
        );
    }
}