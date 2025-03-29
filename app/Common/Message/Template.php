<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Common\Message;

use App\Common\Library\Message\Contract\MsgOptionsAbstract;
use App\Common\Override\Constants;


/**
 * 消息模板引擎
 * 
 
 *
 * @method static string getTitle(int|string $code, array $translate = null)
 * @method static string getBody(int|string $code, array $translate = null)
 * 
 * @method string title() 模板标题
 * @method string smsCode() 短信模板code
 * 
 * @method MsgOptionsAbstract sysMsg(string $app, string|int $orgId, string|int $projectId, string|int $userId, TemplateParam $bodyParams, array $params = [], ) 站内信
 * 
 * @method MsgOptionsAbstract sms( string|array $phoneNumber, TemplateParam $templateParam ) 短信
 * 
 * @method MsgOptionsAbstract aliPush( string $app, string|array $userIds, TemplateParam $bodyParams, array $params = [], ) 阿里推送
 */
#[Constants]
enum Template
{
    /**
     * 测试消息
     * @Title("您有新的消息")
     * @Body("{$userName},你好！欢迎来到{$orgName}。")
     * @SmsCode("SMS_00000000")
     */
    case TestSmsMsg;



    use \Hyperf\Constants\GetterTrait;


    public static function __callStatic(string $name, array $arguments): string|array
    {
        return static::getValue($name, $arguments);
    }

    function __call($name, $arguments)
    {
        $class = $this->matchMsgOption($name);
        if (!$class) {
            if (!str_starts_with($name, 'get')) {
                $name = 'get' . ucfirst($name);
                return static::$name($this->name);
            }
            return static::__callStatic($name, $arguments);
        }
        return $class::ForTemplate($this, ...$arguments);
    }


    function matchMsgOption($name)
    {
        return match (ucfirst($name)) {
            'SysMsg' => SysMsgCommon::class,
            'Sms' => SmsCommon::class,
            'AliPush' => AliPushCommon::class,
            default => ''
        };
    }


    /**
     * 模板消息体
     *
     * @param TemplateParam|null $param
     * @return string
     */
    public function body(TemplateParam $param = null): string
    {
        $body = static::getBody($this->name);
        if ($param) return $param->parse($body);
        return $body;
    }
    
}
