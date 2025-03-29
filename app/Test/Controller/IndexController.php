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

namespace App\Test\Controller;

use App\Common\Exception\BusinessException;
use App\Common\Library\Test\File;
use App\Common\Library\Log\Log;
use App\Test\Constants\BusinessErrorCode;
use App\Test\Model\FileModel;
use App\Test\Model\FileSystemModel;
use GuzzleHttp\Client;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        Log::debug('debug log');

        return $this->success(data: ['method' => $method, 'message' => "Hello {$user}."]);
    }

    #[AuthCheck(2)]
    public function test()
    {
        return $this->success();
    }

}
