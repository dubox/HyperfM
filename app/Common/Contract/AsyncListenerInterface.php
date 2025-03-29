<?php

declare(strict_types=1);


namespace App\Common\Contract;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * 扩展框架自带的 ListenerInterface ，增加 async 方法以辅助实现监听器的异步调用;
 * 具体异步调用通过 defer 实现，defer的介绍可查看官方文档;
 * 因为继承了 ListenerInterface，所以本接口亦可以兼容 ListenerInterface 之场景;
 */
interface AsyncListenerInterface extends ListenerInterface
{
    /**
     * 返回需要异步处理的事件数组，这些事件应先加入监听
     * @return array
     */
    public function async(): array;
}
