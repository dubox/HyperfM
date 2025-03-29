<?php

declare(strict_types=1);

namespace App\Common\Library\Button;


use Hyperf\Utils\Collection;



abstract class ButtonCollection extends Collection
{


    /**
     * 资源按钮集合
     *
     * @param object $resource 需要获取按钮的资源对象，如：订单等
     * @param boolean $showInList 是否在列表中显示 默认false
     */
    function __construct(object $resource ,bool $showInList = false)
    {
        $items = static::getResourceButtons();
        $items = array_filter($items, function (Button $item) use($resource,$showInList){
            return $item->resource($resource)->ifShow($showInList);
        });
        // $arr = [];
        // foreach ($items as $item) {
        //     $arr[$item->getKey()] = $item;
        // }
        parent::__construct(array_values($items));
    }

    abstract static function getResourceButtons():array;
}
