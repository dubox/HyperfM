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

use Hyperf\Utils\MessageBag;
use Throwable;

class Validator extends \Hyperf\Validation\Validator
{

    protected ?Throwable $exception = null;

    /**
     * 全局bail：遇到错误是否继续后续的校验
     *
     * @var boolean
     */
    protected bool $bail = true;

    /**
     * 设置异常对象
     *
     * @param Throwable|null $e
     * @return Throwable|null
     */
    public function exception(?Throwable $e = null): ?Throwable
    {
        if ($e && !$this->exception)
            $this->exception = $e;
        return $this->exception;
    }
    /**
     * 设置或获取bail
     *
     * @param boolean|null $value
     * @return boolean
     */
    public function bail(bool $value = null): bool
    {
        return $this->bail = ($value === null ? $this->bail : $value);
    }

     /**
     * 重写父类方法，实现全局bail（即：遇到错误不再继续后续的校验）
     */
    public function passes(): bool
    {
        $this->messages = new MessageBag();

        [$this->distinctValues, $this->failedRules] = [[], []];

        // We'll spin through each rule, validating the attributes attached to that
        // rule. Any error messages will be added to the containers with each of
        // the other error messages, returning true if we don't have messages.
        foreach ($this->rules as $attribute => $rules) {
            $attribute = str_replace('\.', '->', $attribute);

            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);

                if ($this->shouldStopValidating($attribute)) {
                    if($this->bail)break 2;
                    break;
                }
            }
        }

        // Here we will spin through all of the "after" hooks on this validator and
        // fire them off. This gives the callbacks a chance to perform all kinds
        // of other validation that needs to get wrapped up in this operation.
        foreach ($this->after as $after) {
            call_user_func($after);
        }

        return $this->messages->isEmpty();
    }
}
