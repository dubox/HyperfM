<?php

declare (strict_types=1);

namespace App\Common\Model;

use Hyperf\Utils\HigherOrderTapProxy;

class AliPushDevicesModel extends Model
{
    protected ?string $table = 'AliPushDevices';

    protected array $fillable = ['deviceId','userId','app','platform',];

}