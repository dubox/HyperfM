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

use Hyperf\DbConnection\Db;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class IndexController extends AbstractController
{
    public function index(): ResponseInterface
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return $this->success(200, ['method' => $method, 'message' => "Hello {$user}.", 'id' => get_snowflake_id()]);
    }

    public function healthCheck(): ResponseInterface
    {
        $status = true;

        try {
            // check MySQL connection
            Db::selectOne('SELECT 1');
        } catch (Throwable $e) {
//            $status['mysql'] = 'MySQL connection fail: ' . $e->getMessage();
            $status = false;
        }

        try {
            // check Redis connection
            get_redis()->ping();
        } catch (Throwable $e) {
//            $status['redis'] = 'Redis connection fail: ' . $e->getMessage();
            $status = false;
        }

        return $this->success(data: $status, httpCode: $status ? 200 : 500);
    }
}
