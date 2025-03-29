<?php


namespace App\Common\Library\Message\Driver\AliYun\Push;

use App\Common\Constants\BusinessErrorCode;
use App\Common\Exception\BusinessException;
use App\Common\Model\AliPushDevicesModel;

class Device extends AliPushDevicesModel
{

    static function register(
        $userId,
        $deviceId,
        $app,
        $platform,
    ):static{
        static::unregister($userId,$app,$deviceId,);
        $device = new static;
        $device->userId = $userId;
        $device->deviceId = $deviceId;
        $device->app = $app;
        $device->platform = $platform;
        if(!$device->save())
            throw new BusinessException(BusinessErrorCode::COMMON_ERROR);
        return $device;
    }

    static function unregister(
        $userId,
        $app,
        $deviceId = '',
    ):bool{
        static::where(['userId'=>$userId ,'app'=>$app])
        ->orWhere(fn($query)=>$query->where(['deviceId'=>$deviceId ,'app'=>$app]))
        ->delete();
        return true;
    }

    static function getByUser(array $userIds ,$app = ''){
        return static::whereIn('userId',$userIds)
        ->where(function($query)use($app){
            if($app)
            $query->where('app',$app);
        })
        ->select(['deviceId','app','platform'])->get();
    }

}