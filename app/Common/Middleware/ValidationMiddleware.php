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
namespace App\Common\Middleware;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Validation\Annotation\Scene;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\UnauthorizedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class ValidationMiddleware extends \Hyperf\Validation\Middleware\ValidationMiddleware
{

   /**
    * 重写实现无注解默认方法名为场景scene
    *
    * @param FormRequest $request
    * @param string $class
    * @param string $method
    * @param string $argument
    * @return void
    */
    protected function handleSceneAnnotation(FormRequest $request, string $class, string $method, string $argument): void
    {
        parent::handleSceneAnnotation($request,  $class,  $method,  $argument);

        if(!$request->getScene()){
            $request->scene($method);
        }
    }

   
}
