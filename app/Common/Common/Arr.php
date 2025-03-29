<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Common\Common;


/**
 * 扩展 \Hyperf\Utils\Arr
 */
class Arr extends \Hyperf\Utils\Arr
{
   
    /**
     * 获取 $key 的上 $level 级 key；
     *
     * @param string $key
     * @param integer $level
     * @return string
     */
    static function parentKey(string $key ,int $level = 1):string{
        $arr = explode('.',$key);
        for($i = 1; $i<=$level; $i++)
            array_pop($arr);
        return implode('.', $arr);
    }

    /**
     * 获取 $key 的上 $level 级 key 的 $data 值；
     *
     * @param array $data
     * @param string $key
     * @param integer $level
     * @return mixed
     */
    static function parent(array $data, string $key ,int $level = 1):mixed{
        
        return static::get($data,static::parentKey($key , $level));
    }

    /**
     * 回调函数中可以接受key 的 array_map
     *
     * @param array $arr
     * @param callable $fn fn($value ,$key)
     * @return array
     */
    static function mapWithKey(array $arr ,callable $fn):array{
        return array_map(function($value ,$key)use($fn){
                return $fn($value ,$key);
            },$arr,array_keys($arr));
    }

}
