<?php

declare(strict_types=1);

namespace App\Common\Message;

use App\Common\Library\Message\Options\SmsOptions;


class SmsCommon extends SmsOptions
{


    /**
     * 发送短信
     *
     * @param string $phoneNumber
     * @param string $templateCode
     * @param array $templateParam
     */
    public function __construct(
        string|array $phoneNumber,
        string $templateCode,
        array $templateParam,
    ) {
        parent::__construct(
            phoneNumbers: $phoneNumber,
            signName: m_config("sms.signName"),
            templateCode: $templateCode,
            templateParam: $templateParam
        );
    }


    static public function forTemplate(
        Template $template,
        string|array $phoneNumber,
        TemplateParam $templateParam,
    ) {
        return new static(
            $phoneNumber,
            $template->smsCode(),
            $templateParam->toArray(),
        );
    }
}
