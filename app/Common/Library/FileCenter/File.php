<?php

declare(strict_types=1);

namespace App\Common\Library\FileCenter;
use App\Common\Exception\InternalException;
use App\Common\Model\FileModel;

/**
 * 文件类
 *  @property string $fullPath
 *  @property array $info
 */
class File
{

    protected array $_info;

    function __construct(protected FileModel $fileModel){

    }

    /**
     * 通过id获得一个文件
     * @param mixed $id
     * @return ?File
     */
    public static function get($id):?File
    {
        try{
            return FileModel::getFile($id);
        }catch(InternalException $e){
            return null;
        }
        
    }
    /**
     * 通过id获得一个文件
     * @param mixed $id
     * @return File
     */
    public static function getOrFail($id):File
    {
        
        return FileModel::getFile($id);
        
    }

    function __get($var){
        if(method_exists($this,$var)){
            return $this->$var();
        }
        if ($value = $this->info[$var] ?? null)
            return $value;
    }

    function fullPath(){
        return $this->fileModel->filesystem->baseUri . $this->fileModel->key;
    }


    function info(){
        if (isset($this->_info))
            return $this->_info;

        $info = $this->fileModel->toArray();
        if ($info['filesystem'] ?? 0)
            unset($info['filesystem']);
        return $this->_info = array_merge($info,['baseUri'=>$this->fileModel->filesystem->baseUri]);
    }

    
}
