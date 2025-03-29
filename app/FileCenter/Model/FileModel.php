<?php

declare (strict_types=1);

namespace App\FileCenter\Model;


class FileModel extends \App\Common\Model\FileModel
{

    protected array $fillable = ['key','name','filesystemKey','userId','md5','mimeType','size','desc',];
}