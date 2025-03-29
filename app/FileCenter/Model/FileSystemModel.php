<?php

declare (strict_types=1);

namespace App\FileCenter\Model;

/**
 * 文件系统模型
 * 支持不同的存储方式 本地、oss等
 */
class FileSystemModel extends \App\Common\Model\FileSystemModel
{

    protected array $fillable = ['key','driverType','baseUri','desc','detail'];
}