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

use Hyperf\Config\Config;
use Hyperf\Config\ProviderConfig;
use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * 扩展框架自带的 ConfigFactory ，支持模块下的配置文件读取；
 *  
 */
class ConfigFactory extends \Hyperf\Config\ConfigFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $configPath = BASE_PATH . '/config';
        $config = $this->readConfig($configPath . '/config.php');
        $autoloadConfig = $this->readPaths([$configPath . '/autoload']);
        $moduleConfig = $this->readModules([BASE_PATH . '/app/*/config/']);//支持模块下的配置文件读取
        $merged = array_merge_recursive(ProviderConfig::load(), $config, ...$autoloadConfig, ...[$moduleConfig]);
        return new Config($merged);
    }

    private function readConfig(string $configPath): array
    {
        $config = [];
        if (file_exists($configPath) && is_readable($configPath)) {
            $config = require $configPath;
        }
        return is_array($config) ? $config : [];
    }

    private function readPaths(array $paths): array
    {
        $configs = [];
        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');
        foreach ($finder as $file) {
            $configs[] = [
                $file->getBasename('.php') => require $file->getRealPath(),
            ];
        }
        return $configs;
    }

    /**
     * 读取模块下的配置文件
     *
     * @param array $paths
     * @return array
     */
    private function readModules(array $paths): array
    {
        $configs = [];
        $finder = new Finder();
        $finder->files()->in($paths)->name('config.php');
        foreach ($finder as $file) {
            preg_match_all('/\/app\/(.*)\/config/', $file->getPath(), $match ,PREG_PATTERN_ORDER);
            if($match[1][0]??0)
                $configs['modules'][$match[1][0]] 
                     = require $file->getRealPath();
        }
        return $configs;
    }
}
