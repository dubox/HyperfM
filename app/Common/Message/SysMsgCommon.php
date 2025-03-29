<?php

declare(strict_types=1);

namespace App\Common\Message;

use App\Common\Library\Message\Options\SysMsgOptions;

class SysMsgCommon extends SysMsgOptions
{

   
    static public function forTemplate(
        Template $template,
        string $app,
         string|int $orgId,
         string|int $projectId,
         string|int $userId,
         TemplateParam $bodyParams,
         array $params = [],
    ){
        return new static(
            $app,
            (string)$orgId,
            (string)$projectId,
            (string)$userId,
            $template->title(),
            $template->body($bodyParams),
            $template->name,
            TemplateParam::parseExtraParams($params),
        );
    }
}