<?php

namespace App\Common\Annotation;

use App\Common\Constants\BusinessErrorCode;
use App\Common\Constants\InternalErrorCode;
use App\Common\Exception\InternalException;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;
use Exception;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * 权限校验注解
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AuthCheck extends AbstractAnnotation
{
    /**
     * @param integer $type 1=不校验token+权限 2=只校验token 3=同时校验token+权限
     * @param array $allowedRoles 允许的角色（满足一个即可）,空不校验  eg：[1,2,3]
     * @param array $allowedVerify 允许的认证类型（都满足才能通过），空不校验：1实名认证，2司机认证，3车企认证 eg：[1,2]
     */
    public function __construct(
        public readonly int $type = 1 ,
        public readonly array $allowedRoles = [],
        public readonly array $allowedVerify = [])
    {
        
    }

    /**废弃 */
    function parseRoles($allowedRoles){
        if(!is_array($allowedRoles))
            $allowedRoles = explode(',',$allowedRoles)?:[];
        $roles = [];
        foreach($allowedRoles as $v){
            if(is_numeric($v))
                $roles[$v] = 0;
            else{
                if(!is_array($v))
                    $v = explode(':',$v);
                $roles[$v[0]] = $v[1];
            }
        }
        // $this->allowedRoles = $roles;
    }


    static function getAnnotation($controller, $action):?static{
        // 通过注解获取方法及控制器中的配置
        // 【方法配置 > 类配置】1=不校验token 2=校验token
        $annotations = AnnotationCollector::getClassMethodAnnotation($controller, $action);
        if ($annotations && ($annotations[AuthCheck::class]??0)) {
            return $annotations[AuthCheck::class];
        } else {
            return AnnotationCollector::getClassAnnotation($controller, AuthCheck::class);
        }
    }

}