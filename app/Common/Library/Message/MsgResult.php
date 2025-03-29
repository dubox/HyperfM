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
namespace App\Common\Library\Message;
use App\Common\Library\Message\Contract\MsgOptionsInterface;


class MsgResult
{

    /**
     * 消息体
     * @var MsgOptionsInterface
     */
    public readonly MsgOptionsInterface $message;

    /**
     * 消息发送成功的返回结果
     * @param int|string $id 我们系统的消息id
     * @param int|string $receiptId 第三方回执id
     * @param array $data 其他信息
     */
    public function __construct(
        
        /**
         * 第三方回执id
         */
        readonly int|string $receiptId = 0,
        /**
         * 其他信息
         */
        readonly array $data = [],
        /**
         * 错误信息
         */
        readonly ?MsgException $error = null
    )
    {
        
    }

    public function setMessage(MsgOptionsInterface $message):static{
        $this->message = $message;
        return $this;
    }

}
