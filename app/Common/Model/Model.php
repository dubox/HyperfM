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

namespace App\Common\Model;


use App\Common\Common\NullArray;
use Closure;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\Snowflake\Concern\Snowflake;

abstract class Model extends BaseModel
{
    // 使用雪花 id 作为主键
    use Snowflake;

    // 逻辑删除
    use SoftDeletes;

    // 时间字段
    const CREATED_AT = 'createTime';
    const UPDATED_AT = 'updateTime';
    const DELETED_AT = 'deleteTime';

    public static bool $snakeAttributes = false;

    private array $defaultCasts = [
        'id' => 'string',
        'userId' => 'string',
    ];

    public function __construct(array $attributes = [])
    {
        $this->casts = array_merge($this->defaultCasts, $this->casts ?? []);
        parent::__construct($attributes);
    }


    public static function query(Closure $callback = null)
    {
        $query = parent::query();
        if ($callback)
            $callback($query);
        return $query;
    }


    public function relationsToArray(): array
    {
        $relations = parent::relationsToArray();
        foreach ($relations as $key => &$relation) {
            if ($relation === null) {
                $relation = new NullArray;
            }
        }
        return $relations;
    }

    // /**
    //  * Summary of toArray
    //  * @param bool $full 是否包含关联模型数据，默认：false
    //  * @return array
    //  */
    // public function toArray(bool $full = false): array
    // {
    //     if ($full)
    //         return parent::toArray();
    //     return $this->attributesToArray();
    // }

}
