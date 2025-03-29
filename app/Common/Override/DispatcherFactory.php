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
namespace App\Common\Override;

use Symfony\Component\Finder\Finder;

/**
 * 覆盖 \Hyperf\HttpServer\Router\DispatcherFactory
 * - 增加对模块路由文件的支持
 */
class DispatcherFactory extends \Hyperf\HttpServer\Router\DispatcherFactory
{
    public function __construct()
    {
        $this->loadModuleRoutes();
        parent::__construct();
    }

    protected function loadModuleRoutes()
    {
        $finder = new Finder();
        $finder->files()->in(BASE_PATH . '/app/*/config/')->name('routes.php');

        $paths = [];
        foreach ($finder as $file) {
            $paths[] = $file->getRealPath();
        }
        $this->routes = array_merge($this->routes, $paths);
    }
   
}
