<?php

declare(strict_types=1);

namespace App\Common\Message;

use App\Admin\Model\OrderModel;
use App\Common\Common\Arr;
use App\Common\Library\Message\Contract\MsgOptionsAbstract;
use App\Common\Model\RequirementModel;
use App\Common\Model\UserModel;

/**
 * 模板参数
 * 
 */
class TemplateParam extends MsgOptionsAbstract
{


    /**
     * 模板参数
     *
     * @param string $projectName 项目名称：
     * @param string $settlementName 结算部门：
     * @param string $carCategoryName 车型分类：
     * @param string $workload 工作类型：
     * @param string $workStartTime 进场时间：
     * @param string $deliverTime 送达时间：
     * @param string $carAmount 车辆数量：
     * @param string $carNumber 车牌号：
     */
    public function __construct(
        public readonly string $projectName = '',
        public readonly string $settlementName = '',
        public readonly string $carCategoryName = '',
        public readonly string $workload = '',
        public readonly string $workStartTime = '',
        public readonly string $deliverTime = '',
        public readonly string $carAmount = '',
        public readonly string $carNumber = '',
    ) {
    }

    static function make(
        $projectName = '',
        $settlementName = '',
        $carCategoryName = '',
        $workload = '',
        $workStartTime = '',
        $deliverTime = '',
        $carAmount = '',
        $carNumber = '',
    ) {
        $data = func_get_args();
        foreach ($data as &$arg) {
            $arg = (string)$arg;
        }
        return new static(...$data);
    }

    /**
     * (待定)
     *
     * @param RequirementModel|OrderModel $entry
     * @param UserModel|null $user
     * @return void
     */
    // static function makeFromObjects(
    //     RequirementModel|OrderModel $entry,
    //     UserModel $user = null,
    // ){
    //     $requirement = $order = null;
    //     if(match_class($entry,RequirementModel::class))$requirement = $entry;
    //     if(match_class($entry,OrderModel::class))$order = $entry;
    //     if(!$requirement && $order)$requirement = $order->requirement;
    //     return new static(
    //         $requirement?->project?->name??'',
    //         $requirement?->settlementOrg?->name??'',
    //         $requirement?->carCategory?->name??'',
    //          m_config('Common:workloads.name')[$requirement?->workload??0]??'',
    //          $requirement?->workStartTime??'',
    //          $requirement?->workStartTime??'',
    //          $carAmount = '',
    //          $carNumber = '',
    //     );
    // }


    /**
     * 解析模板
     *
     * @param string $template
     * @return string
     */
    function parse(string $template = ''): string
    {
        $params = $this->toArray();
        extract($params);
        return eval('return "' . $template . '";');
    }

    //废弃
    static function parseEntityParams($params)
    {
        $arr = ['entities' => []];
        Arr::mapWithKey($params, function ($one, $key) use (&$arr) {
            if (is_object($one)) {
                $arr['entities'][] = [
                    'name' => str_replace('Model', '', $one ? get_class_basename($one) : ''),
                    'id' => $one->id ?? 0,
                ];
            } else {
                $arr[$key] = $one;
            }
        });
        return $arr;
    }


    static function parseExtraParams($params){
        return array_merge(
            [
                'orderId'=>'',
                'requirementId'=>'',
                'approvalRecordId'=>'',
                'workload'=>0,
                'orderFeesFixLogId'=>'',
                'carLeaveRecordId'=>'',
            ],
        ...Arr::mapWithKey($params,function($one ,$key){
            return match(true){
                is_object($one) => [lcfirst( str_replace('Model','',$one?get_class_basename($one):'').'Id') => (string)($one->id??'')],
                match_class($one ,RequirementModel::class) => [
                    'workload'=>$one->workload,
                ],
                default => [$key => $one]
            };
        }));
    }
}
