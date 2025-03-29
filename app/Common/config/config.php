<?php

declare(strict_types=1);
/**
 * 模块配置文件
 *  -定义本模块的业务配置信息
 */

return [
    'ali_sts_roles'=> [
        'oss_upload'=>[
            'roleArn' => env('ALIBABA_OSS_ASSUME_ROLE_ARN', ''),
            'roleSessionName' => env('ALIBABA_OSS_ASSUME_ROLE_NAME', ''),
        ]
    ],
    'sms'=>[
        'signName' => '卖拐科技',
    ],
    'captcha' => [
        'lockTime' => 60, // 同一手机号+获取位置，60秒内只能发送一次
        'cacheTime' => 5 * 60, // 同一手机号+获取位置，验证码5分钟内有效【5分钟内不重新生成】
        'signName' => '卖拐科技',
        'templateCode' => 'SMS_xxxxxxx'
    ],
    'ali_push_keys' =>[
        'driver' => ['ANDROID'=>env('ALI_PUSH_ANDROID_DRIVER_KEY',''),'iOS'=>env('ALI_PUSH_IOS_DRIVER_KEY','')],
        'car_company' => ['ANDROID'=>env('ALI_PUSH_ANDROID_CC_KEY',''),'iOS'=>env('ALI_PUSH_IOS_CC_KEY','')],
    ],
    'wechat' => [
        'appId' => '',
        'secret' => '',
    ]
];
