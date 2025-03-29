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

use App\Common\Constants\ErrorCode;
use Attribute;
use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\AnnotationReader;
use Hyperf\Constants\ConstantsCollector;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Utils\Arr;
use ReflectionClass;
/**
 * 扩展框架自带的 Constants 注解
 *  -优化对 ErrorCode 的支持;
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Constants extends AbstractAnnotation
{
    public function collectClass(string $className): void
    {
        $reader = new AnnotationReader();
        $ref = new ReflectionClass($className);
        $classConstants = $ref->getReflectionConstants();
        $data = $reader->getAnnotations($classConstants);
        
        //处理错误码的收集，合并到一个类下面,防止定义重复的code
        if (match_class($className, ErrorCode::class)){
            $className = AbstractConstants::class;
        }
        if (ConstantsCollector::has($className))
            $data = Arr::merge($data, ConstantsCollector::get($className));
            
        ConstantsCollector::set($className, $data);
    }
}
