<?php
declare(strict_types=1);


use App\Common\Override\EventDispatcherFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Contract\ConfigInterface;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    EventDispatcherInterface::class => EventDispatcherFactory::class,
    DispatcherFactory::class => \App\Common\Override\DispatcherFactory::class,
    ConfigInterface::class => \App\Common\Override\ConfigFactory::class,
    // \Hyperf\HttpServer\Contract\ResponseInterface::class => App\Common\Override\Response::class
];
