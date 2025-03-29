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
namespace App\Common\Listener;

use Hyperf\Event\Annotation\Listener;
use App\Common\Contract\AsyncListenerInterface;
use App\Common\Event\TestEvent;

#[Listener]
class TestListener implements ListenerInterface
{
   

    public function listen(): array
    {
        return [
            TestEvent::class,
        ];
    }

    /**
	 * 返回需要异步处理的事件数组
	 * @return array
	 */
	public function async(): array {
        return $this->listen();
	}

    /**
     * 上面async()返回的事件将在异步中执行
     * @param object $event
     */
    public function process(object $event): void
    {
      //事件处理函数将在异步中执行
      echo "TestListener process \n";
    }
}
