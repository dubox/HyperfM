<?php

declare(strict_types=1);

/***
 * 各模块自有的路由配置文件
 *  -这些配置会和最外层以及其他模块的配置合并；
 */

use Hyperf\HttpServer\Router\Router;
use App\FileCenter\Controller as C;

Router::addGroup('/FileCenter', function () {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', C\IndexController::class . '@index');
    Router::addRoute(['GET', 'POST'], '/add', C\IndexController::class . '@add');
    Router::addRoute(['GET', 'POST'], '/filesystemList', C\IndexController::class . '@filesystemList');
    Router::addRoute(['GET'], '/getFileContent', C\IndexController::class . '@getFileContent');
});