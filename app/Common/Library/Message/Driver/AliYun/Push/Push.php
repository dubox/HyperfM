<?php


namespace App\Common\Library\Message\Driver\AliYun\Push;

use AlibabaCloud\SDK\Push\V20160801\Models\PushRequest;
use AlibabaCloud\SDK\Push\V20160801\Push as AliPush;
use AlibabaCloud\Tea\Exception\TeaError;
use App\Common\Library\Message\Contract\MsgDriverInterface;
use App\Common\Library\Message\Contract\MsgOptionsInterface;
use App\Common\Library\Message\MsgError;
use App\Common\Library\Message\MsgException;
use App\Common\Library\Message\MsgResult;
use App\Common\Library\Message\Options\SmsOptions;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Push\V20160801\Models\PushMessageToiOSRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Exception;

class Push implements MsgDriverInterface
{

    protected AliPush $client;


    protected function client()
    {
        if (isset($this->client))
            return $this->client;
        $config = new Config([
            "accessKeyId" => env('ALIBABA_CLOUD_ACCESS_KEY_ID', ''),
            "accessKeySecret" => env('ALIBABA_CLOUD_ACCESS_KEY_SECRET', ''),
        ]);
        $config->regionId = "cn-hangzhou";
        return $this->client = new AliPush($config);
    }

    /**
     * 发送
     * @param SmsOptions $options
     * @throws MsgException
     * @return MsgResult
     */
    public function send(MsgOptionsInterface $options): MsgResult
    {
        try {
            $client = $this->client();
            
            if(($options['targetValue']??'') == ''){
                throw MsgError::NO_RECEIVER->exception($options->type());
            }

            $request = new PushRequest($options->get());

            $result = $client->push($request);
            if ($result?->statusCode == 200 && $result?->body?->messageId??0) {
                return new MsgResult(
                    receiptId: $result->body->messageId,
                    data:$result->body->toMap()
                );
            }
            if($result?->body){
                return new MsgResult(
                    receiptId: $result->body?->messageId??0,
                    data:$result->body->toMap(),
                    error: MsgError::API_ERR->exception($options->type())
                );
            }
            throw MsgError::NET_ERR->exception($options->type());
        }catch(MsgException $e){
            throw $e;
        }
    }
}
