<?php

declare(strict_types=1);

namespace App\Common\Library\Button;

use Closure;
use Hyperf\Context\Context;
use Hyperf\Contract\Arrayable;

/**
 * 按钮
 * 根据权限、业务逻辑等判断按钮是否显示
 * @property mixed $resource
 */
abstract class Button implements Arrayable
{

    function __construct(
        public readonly string $name,
        public readonly array $auth,
        protected ?Closure $otherCondition = null
    ) {
    }

    function __get($name){
        if(method_exists($this, $name)){
            return $this->$name();
        }
    }
    function resource($resource = null){
        if($resource){
            Context::set(static::class.':resource', $resource);
            return $this;
        }
        return Context::get(static::class.':resource');
    }

    /**
     * 判断按钮是否显示
     *
     * @param string $showInWhere 显示的位置：列表 SHOW_IN_LIST，详情 SHOW_IN_DETAIL
     * @return boolean
     */
    function ifShow(bool $showInList = false): bool
    {
        if ($showInList && !$this->showInList()) return false;
        if (!$this->otherCondition) return true;
        return (bool)($this->otherCondition)($this, $this->resource);
    }

    function getKey(): string
    {
        $class = explode('\\', $this->auth[0]) ?: [''];
        $class = str_replace('Controller', '', array_pop($class));
        return lcfirst($class) . ucfirst($this->auth[1] ?? '') . 'Btn';
    }

    abstract function showInList():bool;

    function toArray(): array
    {

        return [
            'name' => $this->name,
            'key' => $this->getKey(),
            'showInList' => $this->showInList(),
        ];
    }
}
