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

namespace App\Common\Controller;

use App\Common\Library\Log\Log;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Collection;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    /**
     * success
     * @param mixed $code
     * @param mixed $data
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function success(mixed $code = 10000, mixed $data = [], string $message = '操作成功', $httpCode = 200)
    {
        $backData = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        // 结束时触发保存日志
        Log::save();

        if ($httpCode != 200) {
            $this->response = $this->response->withStatus($httpCode);
        }

        return $this->response->json($backData);
    }

    /**
     * failed
     * @param int|string $code
     * @param string $message
     * @param array $errors
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function failed(int|string $code = 0, string $message = 'fail', array $errors = [])
    {
        $default = [
            'code' => $code,
            'message' => $message,
        ];

        // 结束时触发保存日志
        Log::save();
        return $this->response->json(array_merge($default, $errors ? ['errors' => $errors] : []));
    }
}
