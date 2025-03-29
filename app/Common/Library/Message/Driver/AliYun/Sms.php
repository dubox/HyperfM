<?php


namespace App\Common\Library\Message\Driver\AliYun;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\Tea\Exception\TeaError;
use App\Common\Library\Message\Contract\MsgDriverInterface;
use App\Common\Library\Message\Contract\MsgOptionsInterface;
use App\Common\Library\Message\MsgError;
use App\Common\Library\Message\MsgException;
use App\Common\Library\Message\MsgResult;
use App\Common\Library\Message\Options\SmsOptions;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Exception;
use Throwable;

class Sms implements MsgDriverInterface
{

    protected Dysmsapi $client;


    protected function client()
    {
        if (isset($this->client))
            return $this->client;
        $config = new Config([
            // 必填，您的 AccessKey ID
            "accessKeyId" => env('ALIBABA_CLOUD_ACCESS_KEY_ID', ''),
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => env('ALIBABA_CLOUD_ACCESS_KEY_SECRET', ''),
        ]);
        // 访问的域名
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return $this->client = new Dysmsapi($config);
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

            if(($options['phoneNumbers']??'') == ''){
                throw MsgError::NO_RECEIVER->exception($options->type());
            }

            $client = $this->client();
            $sendSmsRequest = new SendSmsRequest($options->get());
            $result = $client->sendSmsWithOptions($sendSmsRequest, new RuntimeOptions([]));
            if ($result->statusCode == 200 && $result->body->code == 'OK') {
                return new MsgResult(
                    receiptId:$result->body->bizId,
                    data:$result->body->toMap()
                );
            }
            if ($result->body?->code)
                throw MsgError::API_ERR->exception($options->type() ,$result->body->code . ':' . $result->body->message);
            throw MsgError::NET_ERR->exception($options->type());
            
        }catch(MsgException $e){
            throw $e;
        }

    }
}