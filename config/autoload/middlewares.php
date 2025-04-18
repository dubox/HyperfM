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
return [
    'http' => [
        \App\Common\Middleware\RequestMiddleware::class,
        // \Hyperf\Validation\Middleware\ValidationMiddleware::class,
        // \App\Common\Middleware\ValidationMiddleware::class,
    ],
];
