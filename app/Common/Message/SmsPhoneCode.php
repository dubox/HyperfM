<?php

declare(strict_types=1);

namespace App\Common\Message;
use App\Common\Library\Message\Options\SmsOptions;

/**
 * 发送短信验证码
 * eg: Sender::send(new SmsPhoneCode('15191xxxx43',895323));
 */
class SmsPhoneCode extends SmsOptions
{

    
    /**
     * 发送验证码
     * @param string $phoneNumbers 手机号
     * @param string $code 验证码
     */
    public function __construct(
        string $phoneNumbers,
        string $code
    ){
       parent::__construct(
           phoneNumbers: $phoneNumbers,
           signName: m_config("captcha.signName"),
           templateCode: m_config("captcha.templateCode"),
           templateParam: ['code' => $code]
       );
    }

    
}