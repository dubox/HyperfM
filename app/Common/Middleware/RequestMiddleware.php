<?php

declare(strict_types=1);

namespace App\Common\Middleware;

use App\Common\Annotation\AuthCheck;
use App\Common\Library\Log\Log;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Router\Dispatched;

class RequestMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container, ServerRequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 增加一个请求id
        $request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) {
            $request = $request->withHeader('x-request-id', $this->getRequestId());
            return $request;
        });

        // 利用协程上下文存储请求开始的时间，用来计算程序执行时间
        Context::set('request_start_time', (new \DateTime())->format('Y-m-d H:i:s.u'));

        // 记录访问日志
        Log::access("access log record");

        return $handler->handle($request);
    }

    protected function getRequestId()
    {
        $headers = $this->request->getHeaders();

        // 定义REQUEST_ID，如果请求头中存在则使用传递的值，如果请求头中不存在则创建
        if (isset($headers['x-request-id'])) {
            $requestId = $headers['x-request-id'];
        } else {
            $requestId = uuid();
        }

        return $requestId;
    }
}
