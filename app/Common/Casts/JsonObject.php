<?php

namespace App\Common\Casts;

use Hyperf\Contract\CastsAttributes;

class JsonObject implements CastsAttributes
{
    /**
     * 将取出的数据进行转换
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * 转换成将要进行存储的值
     */
    public function set($model, $key, $value, $attributes)
    {
        if(is_array($value)){
            $value = json_encode((object)$value);
        }
        return  $value;
    }
}
