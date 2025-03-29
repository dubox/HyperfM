<?php

// This file is auto-generated, don't edit it. Thanks.
namespace App\Common\Library\AliYun;

use AlibabaCloud\SDK\Sts\V20150401\Sts as AliSts;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
// use AlibabaCloud\Credentials\Credential\Config;
use AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

class Sts {

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return AliSts Client
     */
    public static function createClient(array $options = []){

        $options = array_merge([
            // 必填，您的 AccessKey ID
            "accessKeyId" => env('ALIBABA_STS_ACCESS_KEY_ID',''),
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => env('ALIBABA_STS_ACCESS_KEY_SECRET',''),
            'endpoint'=>'sts.cn-hangzhou.aliyuncs.com',
        ], $options);
        $config = new Config($options);
        
        return new AliSts($config);
    }

    /**
     * 获取临时角色
     * @param mixed $options
     * @return \AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleResponse
     */
    public static function assumeRole($options = []){
        $client = self::createClient($options);
        $assumeRoleRequest = new AssumeRoleRequest($options);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            return $client->assumeRoleWithOptions($assumeRoleRequest, $runtime);
            
        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            
            throw $error;
        }
    }
}

