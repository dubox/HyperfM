<?php

declare(strict_types=1);

namespace App\Common\Library\Message\Options;
use App\Common\Library\Message\Contract\MsgOptionsAbstract;
use App\Common\Library\Message\MsgType;

/**
 * 站内信
 */
class SysMsgOptions extends MsgOptionsAbstract
{

    protected MsgType $__type = MsgType::BanLiSysMsg;


    /**
     * 司机端app
     */
    const APP_DRIVER = 'driver';

    /**
     * 车企端app
     */
    const APP_CAR_COMPANY = 'car_company';

    /**
     * 管理端app
     */
    const APP_PROJECT_MANAGE = 'project_manage';

    /**
     * 站内信
     *
     * @param string $app 客户端标识
     * @param string $orgId 组织（上游是分公司）
     * @param string $projectId 项目
     * @param string $userId 用户id
     * @param string $title 标题
     * @param string $body 内容
     * @param array $params = [] 自定义参数
     */
    public function __construct(
        protected string $app,
        protected string|int $orgId,
        protected string|int $projectId,
        protected string|int $userId,
        protected string $title = '',
        protected string $body = '',
        protected string $event = '',
        protected array $params = [],
    ){
        
    }

    public function toArray():array{

        return parent::toArray();
        
    }
    
}