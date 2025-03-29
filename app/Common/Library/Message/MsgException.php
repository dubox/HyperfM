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
namespace App\Common\Library\Message;

use Hyperf\Server\Exception\ServerException;
use Throwable;

class MsgException extends ServerException
{

    protected int $baseCode = 5000000;


    public function __construct(public readonly MsgType $msgType, public readonly MsgError $msgError, int $code = 0, string $message = null, Throwable $previous = null)
    {
        parent::__construct($message, (int)$code + $this->baseCode, $previous);
    }
}
