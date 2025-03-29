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
namespace App\Common\Override\Validation;

/**
 * 扩展框架自带的 Rule 
 *  增加 pass 验证器;
 */
class Rule extends \Hyperf\Validation\Rule
{
   
    /**
     * Get a required_if constraint builder instance.
     */
    public static function pass(bool|callable $callback)
    {
        return new Pass($callback);
    }

  
}
