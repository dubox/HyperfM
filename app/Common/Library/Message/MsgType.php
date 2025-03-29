<?php

declare(strict_types=1);

namespace App\Common\Library\Message;
use App\Common\Library\Message\Contract\MsgDriverInterface;
use App\Common\Library\Message\Driver\AliYun\Push\Push;
use App\Common\Library\Message\Driver\AliYun\Sms;
use App\Common\Library\Message\Driver\BanLi\SysMsg;
use App\Common\Library\Message\Driver\WeChat\MiniApp;

/**
 * 消息类型
 *  - 目前支持的消息类型有：短信、阿里移动推送、微信公众号、微信小程序、站内信
 *  - 消息类型与驱动类型一一对应: driverType = msgType
 */
enum MsgType:string
{
    /**
     * 短信
     * driverType = msgType
     */
    case SMS = 'SMS';

    /**
     * 阿里移动推送
     */
    case AliPush = 'AliPush';

    /**
     * 微信公众号
     */
    case WeOfficialAccount = 'WeOfficialAccount';

    /**
     * 微信小程序
     */
    case MiniApp = 'MiniApp';

    /**
     * 站内信
     */
    case BanLiSysMsg = 'BanLiSysMsg';



    public function get():MsgDriverInterface{
        $driver = match ($this) {
            self::SMS => Sms::class,
            self::AliPush => Push::class,
            self::MiniApp => MiniApp::class,
            self::WeOfficialAccount => '',
            self::BanLiSysMsg => SysMsg::class,
        };
        return container($driver);
    }
}