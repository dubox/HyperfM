<?php

declare (strict_types=1);

namespace App\Common\Model;


class FileSystemModel extends Model
{
    protected ?string $table = 'FileSystem';

    protected array $casts = ['detail'=>'array'];
}