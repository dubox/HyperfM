<?php

declare(strict_types=1);


namespace App\Common\Library\Message\Contract;
use App\Common\Library\Message\MsgResult;


interface MsgDriverInterface
{
    /**
     * 执行消息发送
     * @param MsgOptionsInterface $options
     * @return MsgResult
     */
    public function send(MsgOptionsInterface $options): MsgResult;

}
