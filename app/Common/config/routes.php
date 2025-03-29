<?php

declare(strict_types=1);

/***
 * 各模块自有的路由配置文件
 *  -这些配置会和最外层以及其他模块的配置合并；
 */

use Hyperf\HttpServer\Router\Router;
use App\Common\Controller as C;


Router::addGroup('/Common', function () {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', C\IndexController::class . '@index');

    Router::addGroup('/Test/', function () {
        Router::addRoute(['GET', 'POST'], 'index', C\TestController::class . '@index');
    });
    Router::addRoute(['GET'], '/healthCheck', C\IndexController::class . '@healthCheck');

});