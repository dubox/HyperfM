<?php

declare(strict_types=1);

namespace App\Common\Library\Message;

use App\Common\Library\Log\Console;
use App\Common\Library\Message\Contract\MsgOptionsInterface;
use App\Common\Model\MsgLogModel;
use Throwable;

/*
eg:
    Sender::send(
            new SmsOptions(
                phoneNumbers: '1300000000',
                signName: '卖拐科技',
                templateCode: 'SMS_0000000',
                templateParam:['code'=>1234]
            )
        );
*/

/**
 * 消息发送调度器
 */ 
class Sender
{

    /**
     * 消息发送调度
     * @param MsgOptionsInterface $message
     * @return MsgResult[]
     */
    static function send(MsgOptionsInterface $message):array{
        if(!$batches = $message->batches()){
            $batches = [$message];
        }
        return array_map(function($one){
            return static::doSend($one);
        },$batches);
    }

    /**
     * 消息发送调度
     * @param MsgOptionsInterface $message
     * @return MsgResult
     */
    static protected function doSend(MsgOptionsInterface $message):MsgResult{

        //设置消息id
        $message->id(get_snowflake_id());
        try{
            //获取消息driver
            $driver = $message->type()->get();

            //执行发送，并将原消息追加进返回结果
            $res = $driver->send($message)->setMessage($message);
        }catch(MsgException $e){
            $res = (new MsgResult(error:$e))->setMessage($message);
        }catch(Throwable $e){
            Console::error($e);
            $res = (new MsgResult(error:MsgError::CLIENT_ERR->exception($message->type(), $e->getMessage() ,$e)))->setMessage($message);
        }
        static::log($res);
        return $res;
    }

    /**
     * 记录日志
     * @param MsgResult $res
     * @return bool
     */
    protected static function log(MsgResult $res):bool{
        $logM = new MsgLogModel;
        $logM->id = $res->message->id();
        $logM->type = $res->message->type()->value;
        $logM->driverType = $res->message->type()->name;
        $logM->sendData = $res->message->get();
        $logM->receiptId = $res->receiptId;
        $logM->receiveData = $res->data;
        $logM->errorCode = $res->error?->getCode()??0;
        $logM->errorMsg = $res->error?->getMessage()??'';
        return $logM->save();
    }
}