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
namespace Hyperf\Database\Model;

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection as BaseCollection;
use Hyperf\Utils\Str;
use RuntimeException;

/**
 * @template TKey of array-key
 * @template TModel of \Hyperf\Database\Model\Model
 *
 * @extends BaseCollection<TKey, TModel>
 */
class Collection extends BaseCollection implements CompressInterface
{
    /**
     * Find a model in the collection by key.
     *
     * @template TFindDefault
     *
     * @param mixed $key
     * @param TFindDefault $default
     * @return static<TKey|TModel>|TFindDefault|TModel
     */
    public function find($key, $default = null)
    {
        if ($key instanceof Model) {
            $key = $key->getKey();
        }

        if ($key instanceof Arrayable) {
            $key = $key->toArray();
        }

        if (is_array($key)) {
            if ($this->isEmpty()) {
                return new static();
            }

            return $this->whereIn($this->first()->getKeyName(), $key);
        }

        return Arr::first($this->items, function ($model) use ($key) {
            return $model->getKey() === $key;
        }, $default);
    }

    /**
     * Load a set of relationships onto the collection.
     *
     * @param array<array-key, (callable(\Hyperf\Database\Model\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function load($relations)
    {
        if ($this->isNotEmpty()) {
            if (is_string($relations)) {
                $relations = func_get_args();
            }

            $query = $this->first()->newQueryWithoutRelationships()->with($relations);

            $this->items = $query->eagerLoadRelations($this->items);
        }

        return $this;
    }

    /**
     * Load a set of relationship counts onto the collection.
     *
     * @param array<array-key, (callable(\Hyperf\Database\Model\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function loadCount($relations)
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $models = $this->first()->newModelQuery()
            ->whereKey($this->modelKeys())
            ->select($this->first()->getKeyName())
            ->withCount(...func_get_args())
            ->get();

        $attributes = Arr::except(
            array_keys($models->first()->getAttributes()),
            $models->first()->getKeyName()
        );

        $models->each(function ($model) use ($attributes) {
            $this->find($model->getKey())->forceFill(
                Arr::only($model->getAttributes(), $attributes)
            )->syncOriginalAttributes($attributes);
        });

        return $this;
    }

    /**
     * Load a set of relationships onto the collection if they are not already eager loaded.
     *
     * @param array<array-key, (callable(\Hyperf\Database\Model\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function loadMissing($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        foreach ($relations as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }

            $segments = explode('.', explode(':', $key)[0]);

            if (Str::contains($key, ':')) {
                $segments[count($segments) - 1] .= ':' . explode(':', $key)[1];
            }

            $path = array_combine($segments, $segments);

            if (is_callable($value)) {
                $path[end($segments)] = $value;
            }

            $this->loadMissingRelation($this, $path);
        }

        return $this;
    }

    /**
     * Load a set of relationships onto the mixed relationship collection.
     *
     * @param string $relation
     * @param array<array-key, (callable(\Hyperf\Database\Model\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function loadMorph($relation, $relations)
    {
        $this->pluck($relation)
            ->filter()
            ->groupBy(function ($model) {
                return get_class($model);
            })
            ->filter(function ($models, $className) use ($relations) {
                return Arr::has($relations, $className);
            })
            ->each(function ($models, $className) use ($relations) {
                $className::with($relations[$className])
                    ->eagerLoadRelations($models->all());
            });

        return $this;
    }

    /**
     * Add an item to the collection.
     *
     * @param TModel $item
     * @return $this
     */
    public function add($item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Determine if a key exists in the collection.
     *
     * @param (callable(TModel, TKey): bool)|TModel|string  $key
     * @param mixed $operator
     * @param mixed $value
     */
    public function contains($key, $operator = null, $value = null): bool
    {
        if (func_num_args() > 1 || $this->useAsCallable($key)) {
            return parent::contains(...func_get_args());
        }

        if ($key instanceof Model) {
            return parent::contains(function ($model) use ($key) {
                return $model->is($key);
            });
        }

        return parent::contains(function ($model) use ($key) {
            return $model->getKey() == $key;
        });
    }

    /**
     * Get the array of primary keys.
     *
     * @return array<int, mixed>
     */
    public function modelKeys()
    {
        return array_map(function ($model) {
            return $model->getKey();
        }, $this->items);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param iterable<array-key, TModel> $items
     * @return static<TKey, TModel>
     */
    public function merge($items): BaseCollection
    {
        $dictionary = $this->getDictionary();

        foreach ($items as $item) {
            $dictionary[$item->getKey()] = $item;
        }

        return new static(array_values($dictionary));
    }

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     *
     * @param callable(TModel, TKey): TMapValue $callback
     * @return BaseCollection<TKey, TMapValue>|static<TKey, TMapValue>
     */
    public function map(callable $callback): BaseCollection
    {
        $result = parent::map($callback);

        return $result->contains(function ($item) {
            return ! $item instanceof Model;
        }) ? $result->toBase() : $result;
    }

    /**
     * Reload a fresh model instance from the database for all the entities.
     *
     * @param array<array-key, string>|string $with
     * @return static<TKey, TModel>
     */
    public function fresh($with = [])
    {
        if ($this->isEmpty()) {
            return new static();
        }

        $model = $this->first();

        $freshModels = $model->newQueryWithoutScopes()
            ->with(is_string($with) ? func_get_args() : $with)
            ->whereIn($model->getKeyName(), $this->modelKeys())
            ->get()
            ->getDictionary();

        return $this->map(function ($model) use ($freshModels) {
            return $model->exists && isset($freshModels[$model->getKey()])
                ? $freshModels[$model->getKey()] : null;
        });
    }

    /**
     * Diff the collection with the given items.
     *
     * @param iterable<array-key, TModel> $items
     * @return static<TKey, TModel>
     */
    public function diff($items): BaseCollection
    {
        $diff = new static();

        $dictionary = $this->getDictionary($items);

        foreach ($this->items as $item) {
            if (! isset($dictionary[$item->getKey()])) {
                $diff->add($item);
            }
        }

        return $diff;
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param iterable<array-key, TModel> $items
     * @return static<TKey, TModel>
     */
    public function intersect($items): BaseCollection
    {
        $intersect = new static();

        $dictionary = $this->getDictionary($items);

        foreach ($this->items as $item) {
            if (isset($dictionary[$item->getKey()])) {
                $intersect->add($item);
            }
        }

        return $intersect;
    }

    /**
     * Return only unique items from the collection.
     *
     * @param (callable(TModel, TKey): bool)|string|null  $key
     * @return static<int, TModel>
     */
    public function unique($key = null, bool $strict = false): BaseCollection
    {
        if (! is_null($key)) {
            return parent::unique($key, $strict);
        }

        return new static(array_values($this->getDictionary()));
    }

    /**
     * Returns only the models from the collection with the specified keys.
     *
     * @param null|array<array-key, mixed> $keys
     * @return static<int, TModel>
     */
    public function only($keys): BaseCollection
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        $dictionary = Arr::only($this->getDictionary(), $keys);

        return new static(array_values($dictionary));
    }

    /**
     * Returns only the columns from the collection with the specified keys.
     *
     * @param null|array|TKey $keys
     * @return static<int, mixed>
     */
    public function columns($keys): BaseCollection
    {
        if (is_null($keys)) {
            return new BaseCollection([]);
        }
        $result = [];
        $isSingleColumn = is_string($keys);
        foreach ($this->items as $item) {
            if ($isSingleColumn) {
                $value = $item->{$keys} ?? null;
                $result[] = $value instanceof Arrayable ? $value->toArray() : $value;
            } else {
                $result[] = value(static function () use ($item, $keys) {
                    $res = [];
                    foreach ($keys as $key) {
                        $value = $item->{$key} ?? null;
                        $res[$key] = $value instanceof Arrayable ? $value->toArray() : $value;
                    }

                    return $res;
                });
            }
        }

        return new BaseCollection($result);
    }

    /**
     * Returns all models in the collection except the models with specified keys.
     *
     * @param null|array<array-key, mixed> $keys
     * @return static<int, TModel>
     */
    public function except($keys): BaseCollection
    {
        $dictionary = Arr::except($this->getDictionary(), $keys);

        return new static(array_values($dictionary));
    }

    /**
     * Make the given, typically visible, attributes hidden across the entire collection.
     *
     * @param array<array-key, string>|string $attributes
     * @return $this
     */
    public function makeHidden($attributes)
    {
        return $this->each->addHidden($attributes);
    }

    /**
     * Make the given, typically hidden, attributes visible across the entire collection.
     *
     * @param array<array-key, string>|string $attributes
     * @return $this
     */
    public function makeVisible($attributes)
    {
        return $this->each->makeVisible($attributes);
    }

    /**
     * Append an attribute across the entire collection.
     *
     * @param array|string $attributes
     * @return $this
     */
    public function append($attributes)
    {
        return $this->each->append($attributes);
    }

    /**
     * Get a dictionary keyed by primary keys.
     *
     * @param null|iterable<array-key, TModel> $items
     * @return array<array-key, TModel>
     */
    public function getDictionary($items = null)
    {
        $items = is_null($items) ? $this->items : $items;

        $dictionary = [];

        foreach ($items as $value) {
            $dictionary[$value->getKey()] = $value;
        }

        return $dictionary;
    }

    /**
     * The following methods are intercepted to always return base collections.
     * @param mixed $value
     */

    /**
     * Get an array with the values of a given key.
     *
     * @param array<array-key, string>|string $value
     * @return BaseCollection<int, mixed>
     */
    public function pluck($value, ?string $key = null): BaseCollection
    {
        return $this->toBase()->pluck($value, $key);
    }

    /**
     * Get the keys of the collection items.
     *
     * @return BaseCollection<int, TKey>
     */
    public function keys(): BaseCollection
    {
        return $this->toBase()->keys();
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @template TZipValue
     *
     * @param Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue> ...$items
     * @return BaseCollection<int, BaseCollection<int, TModel|TZipValue>>
     */
    public function zip($items): BaseCollection
    {
        return call_user_func_array([$this->toBase(), 'zip'], func_get_args());
    }

    /**
     * Collapse the collection of items into a single array.
     *
     * @return BaseCollection<int, mixed>
     */
    public function collapse(): BaseCollection
    {
        return $this->toBase()->collapse();
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param int $depth
     * @return BaseCollection<int, mixed>
     */
    public function flatten($depth = INF): BaseCollection
    {
        return $this->toBase()->flatten($depth);
    }

    /**
     * Flip the items in the collection.
     *
     * @return BaseCollection<TModel, TKey>
     */
    public function flip(): BaseCollection
    {
        return $this->toBase()->flip();
    }

    /**
     * Pad collection to the specified length with a value.
     *
     * @template TPadValue
     *
     * @param TPadValue $value
     * @return BaseCollection<int, TModel|TPadValue>
     */
    public function pad(int $size, $value): BaseCollection
    {
        return $this->toBase()->pad($size, $value);
    }

    public function compress(): UnCompressInterface
    {
        if ($this->isEmpty()) {
            return new CollectionMeta(null);
        }

        $class = get_class($this->first());

        $this->each(function ($model) use ($class) {
            if (get_class($model) !== $class) {
                throw new RuntimeException('Collections with multiple model types is not supported.');
            }
        });

        $keys = array_keys($this->getDictionary());

        return new CollectionMeta($class, $keys);
    }

    /**
     * Load a relationship path if it is not already eager loaded.
     *
     * @param \Hyperf\Database\Model\Collection $models
     */
    protected function loadMissingRelation(Collection $models, array $path)
    {
        $relation = array_splice($path, 0, 1);

        $name = explode(':', key($relation))[0];

        if (is_string(reset($relation))) {
            $relation = reset($relation);
        }

        $models->filter(function ($model) use ($name) {
            return ! is_null($model) && ! $model->relationLoaded($name);
        })->load($relation);

        if (empty($path)) {
            return;
        }

        $models = $models->pluck($name);

        if ($models->first() instanceof BaseCollection) {
            $models = $models->collapse();
        }

        $this->loadMissingRelation(new static($models), $path);
    }
}
