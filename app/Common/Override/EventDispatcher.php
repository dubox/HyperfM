<?php

namespace App\Common\Override;

use App\Common\Contract\AsyncListenerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

/**
 * 覆盖扩展框架自带 EventDispatcher ，实现监听器的异步调用;
 * 异步调用通过 defer 实现，defer的介绍可查看官方文档;
 */
class EventDispatcher extends \Hyperf\Event\EventDispatcher{
    


    public function __construct(
        protected ListenerProviderInterface $listeners,
        protected ?LoggerInterface $logger = null
    ) {
    }


    /**
     * Provide all listeners with an event to process.
     *
     * @param object $event The object to process
     * @return object The Event that was passed, now modified by listeners
     */
    public function dispatch(object $event)
    {
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            $this->execListener($listener, $event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    protected function execListener($listener ,$event){
        if ($this->isAsync($listener, $event)) {
            //异步执行监听器
            defer(function()use($listener ,$event){
                $listener($event);//判断数组
                $this->dump($listener, $event);
            });
        }else{
            //同步执行
            $listener($event);//判断数组
            $this->dump($listener, $event);
        }
    }

    /**
     * 判断监听器在当前事件是否为异步
     * @param mixed $listener
     * @param mixed $event
     * @return bool
     */
    protected function isAsync( $listener,object $event):bool{
        $async_events = [];
        if(is_array($listener) 
            && match_class($listener[0]??'',AsyncListenerInterface::class)){
            $async_events = $listener[0]->async();
        }
        return in_array($event::class ,$async_events);
    }

    protected function dump($listener, object $event)
    {
        if (! $this->logger) {
            return;
        }
        $eventName = get_class($event);
        $listenerName = '[ERROR TYPE]';
        if (is_array($listener)) {
            $listenerName = is_string($listener[0]) ? $listener[0] : get_class($listener[0]);
        } elseif (is_string($listener)) {
            $listenerName = $listener;
        } elseif (is_object($listener)) {
            $listenerName = get_class($listener);
        }
        $this->logger->debug(sprintf('Event %s handled by %s listener.', $eventName, $listenerName));
    }
}