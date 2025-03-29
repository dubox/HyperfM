<?php

namespace App\Common\Listener;

use App\Common\Exception\BusinessException;
use App\Common\Exception\InternalException;
use App\Common\Library\Log\Console;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Throwable;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /**  @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        /**
         * 注册 pass 验证器  pass:p1,p2
         *  p1 为需要检查的其他字段，
         *  p2 与p1比较的值，不传p2则检查p1的值
         */
        $validatorFactory->extend('pass', function ($attribute, $value, $parameters, $validator) {
            try {
                return match (count($parameters)) {
                    1 => is_callable($parameters[0] ?? 0)
                        ? $parameters[0]($attribute, $value, $validator)
                        : (bool)($validator->attributes()[$parameters[0]] ?? false),
                    2 => ($validator->attributes()[$parameters[0]] ?? null) == $parameters[1],
                    default => false,
                };
            } catch (Throwable $e) {
                if (match_class($e, BusinessException::class) || match_class($e, InternalException::class))
                    throw $e;
                $validator->exception($e);
                return false;
            }
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :foo 占位符
        $validatorFactory->replacer('pass', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':pass', $attribute, $message);
        });
    }
}
