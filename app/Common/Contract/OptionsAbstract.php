<?php

declare(strict_types=1);


namespace App\Common\Contract;

/**
 * 参数抽象类
 *  - 用于封装api、内部公共调用等的参数
 *  - 实现了 OptionsInterface 接口
 */
abstract class OptionsAbstract implements OptionsInterface
{
    final public function get(string $key = ''): mixed
    {

        if ($key)
            return $this->$key;
        return $this->toArray();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function offsetExists($offset): bool
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset):mixed
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }
}
