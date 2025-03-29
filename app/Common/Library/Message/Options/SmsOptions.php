<?php

declare(strict_types=1);

namespace App\Common\Library\Message\Options;
use App\Common\Library\Message\Contract\MsgOptionsAbstract;
use App\Common\Library\Message\MsgType;


class SmsOptions extends MsgOptionsAbstract
{

    protected MsgType $__type = MsgType::SMS;

    
    /**
     * 短信发送参数配置
     * @param string|array $phoneNumbers 手机号
     * @param string $signName 签名
     * @param string $templateCode 模板码
     * @param array $templateParam 模板参数
     */
    public function __construct(
        protected string|array $phoneNumbers,
        protected string $signName,
        protected string $templateCode,
        protected array|string $templateParam = [],
        protected string $outId = ''
    ){
        if (is_array($this->phoneNumbers)) {
            $this->phoneNumbers = implode(',', $this->phoneNumbers);
        }
        if (is_array($this->templateParam)) {
            $this->templateParam = json_encode($this->templateParam,1);
        }
    }

    public function toArray():array{

        $this->outId = $this->outId?:$this->id();
        return parent::toArray();
        
    }
    
}