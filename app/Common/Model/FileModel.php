<?php

declare (strict_types=1);

namespace App\Common\Model;
use App\Common\Constants\InternalErrorCode;
use App\Common\Exception\InternalException;
use App\Common\Library\FileCenter\File;


class FileModel extends Model
{
    protected ?string $table = 'File';

    protected array $casts = ['size'=>'int'];

    static function getFile($id)
    {
        $file = static::where('id',$id)->first();
        if (!$file)
            throw new InternalException(InternalErrorCode::FILE_NOT_FOUND);
        return make(File::class,[$file]);
    }


    function filesystem()
    {
        return $this->hasOne(FileSystemModel::class, 'key', 'filesystemKey');
    }
}