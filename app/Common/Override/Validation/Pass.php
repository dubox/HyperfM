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

use Stringable;

/**
         * pass验证器 规则用法：
         *  pass:idCardNumber   //idCardNumber 字段的值等价于 true 时通过 否则不通过
         *  pass:idCardNumber,16    //idCardNumber 字段的值等于 16 时通过 否则不通过
         *  Rule::pass(function(){ return false;})  //匿名函数返回 true 时通过 否则不通过 
         *  ['pass',function($attribute, $value, Validator $validator){}] //同上 （主要用法）
         */
class Pass implements Stringable
{
    /**
     * The condition that validates the attribute.
     *
     * @var bool|callable
     */
    public mixed $condition;

    /**
     * Create a new required validation rule based on a condition.
     */
    public function __construct(bool|callable $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? '' : 'pass';
        }

        return $this->condition ? '' : 'pass';
    }
}
