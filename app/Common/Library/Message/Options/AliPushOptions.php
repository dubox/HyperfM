<?php

declare(strict_types=1);

namespace App\Common\Library\Message\Options;

use App\Common\Constants\BusinessErrorCode;
use App\Common\Exception\BusinessException;
use App\Common\Library\Message\Contract\MsgOptionsAbstract;
use App\Common\Library\Message\Driver\AliYun\Push\Device;
use App\Common\Library\Message\MsgType;


class AliPushOptions extends MsgOptionsAbstract
{

    protected MsgType $__type = MsgType::AliPush;

    /**
     * 司机端app
     */
    const APP_DRIVER = 'driver';

    /**
     * 车企端app
     */
    const APP_CAR_COMPANY = 'car_company';

    protected bool $storeOffline = true;
    protected bool $iOSRemind = true;
    protected bool $androidRemind = true;
    protected string $deviceType = 'ALL';
    protected string $iOSRemindBody = '';
    protected string $androidPopupTitle = '';
    protected string $androidPopupBody = '';
    protected string $jobKey = '';
    protected string $androidExtParameters = '';
    protected string $iOSExtParameters = '';
    protected string $iOSApnsEnv = 'DEV';
    protected string|array $appKey = [];
    protected string $targetValue = '';
    protected string $target = "DEVICE";
    protected  $devices ;

    /**
     * 阿里移动推送
     *
     * @param string $app 目标app ： AliPushOptions::APP_DRIVER,AliPushOptions::APP_CAR_COMPANY
     * @param string|array $userIds 目标用户id，支持单个和批量
     * @param string $title 消息标题
     * @param string $body 消息内容
     * @param array $params 自定义参数
     * @param string $pushType 消息类型 NOTICE：通知，MESSAGE：消息
     * @param string $target
     */
    public function __construct(
        protected string $app,
         string|array|int $userIds,
        protected string $title ,
        protected string $body ,
         array $params = [],
        protected string $pushType = "NOTICE",
        // protected string $target = "DEVICE",
    ){
        $this->iOSRemindBody = $body;
        $this->androidPopupTitle = $title;
        $this->androidPopupBody = $body;
        $this->androidExtParameters = $this->iOSExtParameters = json_encode($params);

        $this->appKey = match($app){
            static::APP_DRIVER =>  m_config('ali_push_keys.driver'),
            static::APP_CAR_COMPANY =>  m_config('ali_push_keys.car_company'),
            default => throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR,'AliPush app err')
        };

        if(!is_array($userIds))$userIds = [$userIds];

        $this->devices = Device::getByUser($userIds,$app);

        //判断环境 for ios
        if(env('APP_ENV') == 'prod')
        $this->iOSApnsEnv = 'PRODUCT';

    }

    /**
     * 为各平台准备数据
     *
     * @param string $platform
     * @return static
     */
    protected function prepareFor(string $platform):static{
        $this->deviceType = $platform;
        $this->appKey = $this->appKey[$this->deviceType];
        $deviceIds = $this->devices->where('platform',$this->deviceType)->pluck('deviceId')->toArray();
        $this->targetValue = implode(',',$deviceIds);
        unset($this->devices);
        return $this;
    }

    /**
     * 处理android需要的数据
     *
     * @return static
     */
    protected function toAndroid():static{
        //
        return $this->prepareFor('ANDROID');
    }

    /**
     * 处理ios需要的数据
     *
     * @return static
     */
    protected function toIOS():static{
        //
        return $this->prepareFor('iOS');
    }


    /**
     * 分别发送 android 和 ios
     *
     * @return static[]
     */
    public function batches():array{
        if($this->deviceType != 'ALL')
        return [];

        return [
            (clone $this)->toAndroid(),
            (clone $this)->toIOS(),
        ];
    }

    public function toArray():array{

        $this->jobKey = $this->jobKey?:$this->id();
        return parent::toArray();
        
    }
    
}