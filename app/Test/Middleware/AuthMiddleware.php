<?php

declare(strict_types=1);

namespace App\Test\Middleware;

use App\Common\Annotation\AuthCheck;
use App\Common\Constants\BusinessErrorCode;
use App\Common\Exception\BusinessException;
use App\Common\Library\Auth\Authorization;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var HttpResponse
     */
    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取当前路由信息
        [$controller, $action] = explode('@', @request()->getAttribute(Dispatched::class)->handler->callback ?? 'a@b');

        $annotation = AuthCheck::getAnnotation($controller, $action);

        [$checkToken, $checkAuth] = match ($annotation?->type) {
            2 => [true, false],
            3 => [true, true],
            default => [false, false],
        };

        // 判断是否需要登录
        if ($checkToken) {
            //需要验证token 确定用户已登录

            //...验证登录
            // $auth = AuthLogic::set($userinfo);
            // $request = $request->withAttribute('auth', $auth);
            // Context::set(ServerRequestInterface::class, $request);
        }

        if ($checkAuth) {
            if (!($auth ?? 0)) throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, '用户信息错误');
            $auth->check($annotation);//检查用户权限
        }

        return $handler->handle($request);
    }
}
