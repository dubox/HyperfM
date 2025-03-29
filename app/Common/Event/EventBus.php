<?php

namespace App\Common\Event;

use App\Common\Constants\InternalErrorCode;
use App\Common\Exception\BusinessException;
use App\Common\Exception\InternalException;
use App\Common\Library\Log\Console;
use Hyperf\Di\ReflectionManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * 事件总线
 * 所有事件都在这里注册一下，有以下好处：
 * 1.可以做一些统一处理，如果需要的话
 * 2.可以在代码中直接使用事件，而不需要引入事件类，比如：EventBus::TestEvent->dispatch($data);
 * 3.可以充分利用ide的代码提示功能，只需输入EventBus::，就可以看到所有的事件
 * ps：也可以将事件分类定义多个“总线”
 */
enum EventBus: string
{
    /**
     * 测试事件
     * - 触发事件：EventBus::TestEvent->dispatch($data);
     * @param array $data
     */
    case TestEvent =  TestEvent::class;

   




    /**
     * 分发事件
     *
     * @param EventBus $event
     * @param mixed ...$args
     * @return mixed
     */
    public function dispatch( ...$args)
    {
        try{
            return container(EventDispatcherInterface::class)
            ->dispatch(new ($this->value)(
                ...$args
            ));
        }catch(Throwable $e){
            Console::error($e);
        }
        
    }

    /**
     * 批量分发事件
     *
     * @param EventBus[] $events
     * @param mixed ...$args
     * @return mixed
     */
    public static function batchDispatch(array $events, ...$args)
    {
        array_map(function(EventBus $one)use($args){
            $one->dispatch(...$one->argsParse(
                $one->value,
                '__construct',
                $args
            ));
        },$events);
    }

    /**
     * 解析参数：将$args 按照 $method 所需要的参数进行筛选和排序
     *
     * @param string $className
     * @param string $method
     * @param array $args
     * @return array
     */
    protected function argsParse($className, $method, array $args)
    {
        // 获取方法的反射
        $reflectionMethod = ReflectionManager::reflectMethod($className, $method);
        // 获取方法的参数
        $parameters = $reflectionMethod->getParameters();

        // 遍历参数
        $newArgs = [];
        foreach ($parameters as $parameter) {
            // 获取参数的类名
            $argClassName = $parameter->getType()?->getName() ?? '';
            // 如果参数是对象，将其加入$newArgs数组中
            if (class_exists($argClassName)) {
                foreach ($args as $k => $arg) {
                    if (is_a($arg, $argClassName)) {
                        $newArgs[] = $arg;
                        // unset($args[$k]);
                        break;
                    }
                }
            } else {
                throw new InternalException(InternalErrorCode::CUSTOM_ERR, 'Only object args allowed in msg events');
            }
        }
        return $newArgs;
    }
}
