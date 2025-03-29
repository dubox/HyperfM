<?php


namespace App\Common\Library\Message\Driver\WeChat;


use App\Common\Library\Message\Contract\MsgDriverInterface;
use App\Common\Library\Message\Contract\MsgOptionsInterface;
use App\Common\Library\Message\MsgError;
use App\Common\Library\Message\MsgException;
use App\Common\Library\Message\MsgResult;
use App\Common\Library\Message\Options\SmsOptions;
use EasyWeChat\MiniApp\Application;
use EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient;
use Exception;
use Throwable;

class MiniApp implements MsgDriverInterface
{

    protected AccessTokenAwareClient $client;
    protected Application $app;


    protected function client()
    {
        if (isset($this->client))
            return $this->client;
            $config = [
                //TODO app_id需要搞成活的
                'app_id' => 'wx0000000000000',
                'secret' => 'xxxxxxxxx',
                // 'token' => 'easywechat',
                // 'aes_key' => '......',
                'http' => [
                    'throw'  => false, // 状态码非 200、300 时是否抛出异常，默认为开启
                    'timeout' => 5.0,
                    'retry' => true, // 使用默认重试配置
                ],
            ];
            $this->app = new Application($config);
        return $this->client = $this->app->getClient();
    }

    /**
     * 发送
     * @param SmsOptions $options
     * @throws MsgException
     * @return MsgResult
     */
    public function send(MsgOptionsInterface $options):MsgResult
    {
        try {
            $result = $this->request($options);
            $res = $result->toArray();
            if ($result->getStatusCode() == 200 && $res['errcode'] == 0) {
                return new MsgResult(data:$res);
            }
            if ($res['errcode'])
                throw MsgError::API_ERR->exception($options->type() ,$res['errcode'] . ':' . $res['errmsg']);
            throw MsgError::NET_ERR->exception($options->type());
            
        }catch(MsgException $e){
            
            throw $e;
        }catch(Throwable $e){
            
            throw MsgError::CLIENT_ERR->exception($options->type(), $e->getMessage() ,$e);
        }

    }

    protected function request($options ,int $retry = 1){
        $client = $this->client();
        $token = $this->app->getAccessToken();
        
        $result = $client->withAccessToken($token)->postJson('/cgi-bin/message/subscribe/send', $options->get());
        if($retry>0 && ($result->toArray()['errcode']??0) == 40001){
            //token失效，刷新token
            $token->refresh();
            return $this->request($options ,--$retry);
        }
        return $result;
    }
}