<?php

declare(strict_types=1);


namespace App\Common\Library\Message\Contract;
use App\Common\Contract\OptionsAbstract;
use App\Common\Library\Message\MsgType;


abstract class MsgOptionsAbstract extends OptionsAbstract implements MsgOptionsInterface
{
    protected MsgType $__type; 

    public readonly int|string $__id; 

   
    public function type(): MsgType{
        return $this->__type;
    }
   
    /**
     * 设置消息id
     * @return int|string
     */
    public function id(int|string $id = 0):int|string {
        if ($id)
            $this->__id = $id;
        return $this->__id??0;
    }

    public function toArray():array{

        $options = get_object_vars($this);
        foreach($options as $k=>$v){
            if (\Hyperf\Utils\Str::startsWith($k, '__'))
                unset($options[$k]);
        }
        return $options;
        
    }

    /**
     * 分批发送
     *
     * @return static[]
     */
    public function batches():array{
        return [];
    }
}
