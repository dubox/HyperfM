<?php

declare(strict_types=1);

namespace App\Common\Common;
use ArrayAccess;
use Countable;
use Hyperf\Contract\Arrayable;
use Iterator;
use stdClass;
use SimpleXMLElement;
use Stringable;


/**
 * 空数组对象
 * 在行为上基本接近空数组[],同时又是一个对象
 * 
 */
class NullArray  extends SimpleXMLElement implements Arrayable,Countable,Iterator,ArrayAccess
{

    function __construct(){
        parent::__construct('<root></root>');
    }
	
	public function key(): string{
		return '';
	}

    public function toBoolean() {
        return false;
    }

    public function __toString() {
        return '';
    }

	
	public function toArray(): array {
        return [];
	}
	
	
	public function __toArray(): array {
        return $this->toArray();
	}

	public function offsetSet($key, $value): void
    {
    
    }

	public function offsetGet($key):mixed
    {
        return null;
    }

	public function offsetExists($key):bool
    {
        return false;
    }

	public function offsetUnset($key): void
    {
        
    }


	/**
	 * 覆盖 SimpleXMLElement 的写入方法
	 */
	public function addAttribute(string $key, string $value, ?string $namespace = null): void{
		
	}

	/**
	 * 覆盖 SimpleXMLElement 的写入方法
	 */
	public function addChild(string $qualifiedName, ?string $value = null, ?string $namespace = null): ?SimpleXMLElement{
		return null;
	}
	/**
	 * Count elements of an object
	 * This method is executed when using the count() function on an object implementing Countable.
	 * @return int The custom count as an `int`.
	 */
	public function count():int {
		return 0;
	}
	/**
	 * Returns the current element.
	 */
	public function current():SimpleXMLElement {
		return new static;
	}
	
	/**
	 * Move forward to next element
	 * Moves the current position to the next element.
	 * @return void
	 */
	public function next():void {
	}
	
	/**
	 * Rewind the Iterator to the first element
	 * Rewinds back to the first element of the Iterator.
	 * @return void
	 */
	public function rewind():void {
	}
	
	/**
	 * Checks if current position is valid
	 * This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
	 * @return bool The return value will be casted to `bool` and then evaluated. Returns `true` on success or `false` on failure.
	 */
	public function valid():bool {
		return false;
	}
}
