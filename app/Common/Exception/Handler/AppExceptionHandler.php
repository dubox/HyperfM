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

namespace App\Common\Exception\Handler;

use App\Common\Constants\BusinessErrorCode;
use App\Common\Exception\BusinessException;
use App\Common\Exception\InternalException;
use App\Common\Library\Log\Console;
use App\Common\Library\Log\Log;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\Validation\ValidationException;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {

        //输出错误信息
        if (!$throwable instanceof ValidationException)
            $this->stdoutErr($throwable);
        $this->stdoutErr($throwable->getPrevious());

        $data = [];
        $data['code'] = (int)$throwable->getCode();
        $data['message'] = $throwable->getMessage();
        if ($throwable instanceof ValidationException) {
            $data['code'] = BusinessErrorCode::PARAM_ERROR;
            $data['message'] = $throwable->validator->errors()->first();
        } elseif (!($throwable instanceof BusinessException) && config('app_env') != 'dev') {
            $data['message'] = '服务异常，请稍后重试！';
        }

        Log::save();
        return $response->withBody(new SwooleStream(json_encode($data, 1)));
    }

    protected function stdoutErr(?Throwable $throwable)
    {
        if (!$throwable) return;
        // 控制台输出异常信息
        Console::error($throwable);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
