<?php

declare(strict_types=1);

namespace App\Common\Logic;

use App\Common\Annotation\AuthCheck;
use App\Common\Library\Auth\Authorization;
use App\Common\Constants\BusinessErrorCode;
use App\Common\Exception\BusinessException;
use App\Common\Logic\AbstractLogic;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\StdoutLoggerInterface;


/**
 * 权限逻辑
 */
class AuthLogic extends AbstractLogic implements Arrayable
{

    static protected array $pool = [];

    static protected int $poolSize = 100;


    // static function get(int $userId){
    //     return static::$pool[$userId]??null;
    // }

    static function set(UserModel $user): static
    {
        if (!$user->id)
            throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, '用户信息错误');
        $instance = new static($user->append(['avatarUrl']));
        // static::$pool[$user->id] = $instance;
        // if(count(static::$pool) >= static::$poolSize)
        //     array_shift(static::$pool);
        return $instance;
    }


    protected string $token = '';

    protected function __construct(public readonly UserModel $user)
    {
    }


    function __get($name)
    {
        if (method_exists($this, $name))
            return $this->$name();
    }

    /**
     * 检查用户权限
     * @param AuthCheck $annotation 当前访问的类/方法的AuthCheck注解
     */
    function check(AuthCheck $annotation)
    {
        
    }



    function setToken(): static
    {
        $this->token = Authorization::getJWT('Driver', $this->dataForToken());
        return $this;
    }

  

    function toArray(): array
    {
        return [
            // 'token' => $this->token,
            // 'user' => $this->user->toArray(),
        ];
    }
}
