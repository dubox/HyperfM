<?php

declare(strict_types=1);


namespace App\Common\Contract;

use ArrayAccess;
use Hyperf\Contract\Arrayable;


interface OptionsInterface extends Arrayable,ArrayAccess
{

    public function get(string $key = ''): mixed;
}
