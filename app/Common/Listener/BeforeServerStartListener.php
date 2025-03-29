<?php

declare(strict_types=1);
namespace App\Common\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeServerStart;

/**
 * server启动前的事件，早于各worker进程的启动
 * 所以在这里产生的全局数据在worker进程都有效
 */
#[Listener]
class BeforeServerStartListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            BeforeServerStart::class,
        ];
    }

    /**
     * 
     */
    public function process(object $event): void
    {
        echo "BeforeServerStart emitted\n";
    }
}
