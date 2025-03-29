<?php

declare(strict_types=1);

namespace App\Common\Library\Message\Options;
use App\Common\Library\Message\Contract\MsgOptionsAbstract;
use App\Common\Library\Message\MsgType;


class MiniAppOptions extends MsgOptionsAbstract
{

    protected MsgType $__type = MsgType::MiniApp;

    protected string $miniprogram_state = 'formal';
    public function __construct(
        protected string $template_id,
        protected string $touser,
        protected array $data,
        protected string $page = '',
         string $miniprogram_state = ''
    ){
        if($miniprogram_state)$this->miniprogram_state = $miniprogram_state;
    }

    public function toArray():array{

        return parent::toArray();
        
    }
    
}