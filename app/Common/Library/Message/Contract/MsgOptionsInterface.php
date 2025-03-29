<?php

declare(strict_types=1);


namespace App\Common\Library\Message\Contract;
use App\Common\Contract\OptionsInterface;
use App\Common\Library\Message\MsgType;


interface MsgOptionsInterface extends OptionsInterface
{
    /**
     * 获取消息类型
     * @return MsgType
     */
    public function type(): MsgType;

    /**
     * 设置/获取消息id
     * @return int|string
     */
    public function id(int|string $id = 0):int|string ;


    /**
     * 分批发送
     *
     * @return static[]
     */
    public function batches():array;

}
