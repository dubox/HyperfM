<?php

declare(strict_types=1);

namespace App\Common\Message;

use App\Common\Library\Message\Options\AliPushOptions;


class AliPushCommon extends AliPushOptions
{



    static public function forTemplate(
        Template $template,
        string $app,
        string|array|int $userIds,
        TemplateParam $bodyParams,
        array $params = [],

    ) {
        return new static(
            $app,
            $userIds,
            $template->title(),
            $template->body($bodyParams),
            [
                'event' => $template->name,
                'params' => TemplateParam::parseExtraParams($params)
            ],
            //  $pushType = "NOTICE",
        );
    }


    /**
     * 向司机端推送
     *
     * @param string|array $userIds
     * @param string $title
     * @param string $body
     * @param array $params
     * @return static
     */
    static function driver(
        string|array $userIds,
        string $title,
        string $body,
        array $params = [],
    ): static {
        return new static(
            static::APP_DRIVER,
            $userIds,
            $title,
            $body,
            $params
        );
    }

    /**
     * 向车企端推送
     *
     * @param string|array $userIds
     * @param string $title
     * @param string $body
     * @param array $params
     * @return static
     */
    static function carCompany(
        string|array $userIds,
        string $title,
        string $body,
        array $params = [],
    ): static {
        return new static(
            static::APP_CAR_COMPANY,
            $userIds,
            $title,
            $body,
            $params
        );
    }
}
