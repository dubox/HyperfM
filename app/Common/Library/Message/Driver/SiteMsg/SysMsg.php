<?php


namespace App\Common\Library\Message\Driver\SiteMsg;

use App\Common\Library\Message\Contract\MsgDriverInterface;
use App\Common\Library\Message\Contract\MsgOptionsInterface;
use App\Common\Library\Message\MsgError;
use App\Common\Library\Message\MsgException;
use App\Common\Model\SysMsgModel;
use App\Common\Library\Message\MsgResult;
use App\Common\Library\Message\MsgType;
use Throwable;

/**
 * 站内信、系统消息
 */
class SysMsg implements MsgDriverInterface
{

    // protected SysMsgModel $client;


    protected function client()
    {
        return new SysMsgModel;
    }

    /**
     * 发送
     * @param MsgOptionsInterface $options
     * @throws MsgException
     * @return MsgResult
     */
    public function send(MsgOptionsInterface $options):MsgResult
    {
        try {
            if(!($options['userId']??'')){
                throw MsgError::NO_RECEIVER->exception($options->type());
            }
            $client = $this->client();
            if(! $client->send($options->get())){
                throw MsgError::API_ERR->exception(MsgType::SiteMsgSysMsg,'发送失败');
            }
            
            return new MsgResult($client->id,$client->toArray());
        }catch(MsgException $e){
            throw $e;
        }

    }
}