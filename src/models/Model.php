<?php

declare(strict_types=1);

namespace league\models;

use InvalidArgumentException;
use league\components\Db;
use league\components\Query;
use RuntimeException;
use Throwable;
use function array_key_exists;
use function array_keys;
use function in_array;
use function is_array;
use function reset;

/**
 * Class AbstractModel
 * @package league\models
 */
abstract class Model implements RepoInterface
{
    protected $attributes = [];

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     */
    protected function setAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new InvalidArgumentException("No attribute named '$name' configured.");
        }
        return $this->getAttribute($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new InvalidArgumentException("No attribute named '$name' configured.");
        }
        $this->setAttribute($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param int|string|array $where
     * @param array $order
     * @return Model|null
     */
    public static function find($where = null, array $order = []): ?Model
    {
        try {
            $model = new static;

            if (is_array($where)) {
                $model->fetch($where, $order);
            } else {
                $model->fetchByPk($where);
            }

            $model->setId($model->getAttribute($model->getPkName()));

            return $model;

        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param array $where
     * @param array $order
     * @return array
     */
    public static function findAll(array $where = [], array $order = []): array
    {
        $className = static::class;

        $fetched = Db::getInstance()->fetch(
            (new Query())
                ->from($className::tableName())
                ->where($where)
                ->orderBy($order)
        );

        if (!$fetched) {
            return [];
        }

        $results = [];

        foreach ($fetched as $result) {
            $model = new static;

            $attributes = array_keys($model->attributes);

            foreach ($result as $key => $value) {
                if (in_array($key, $attributes, true)) {
                    $model->$key = $value;
                }
            }

            $model->setId($model->getAttribute($model->getPkName()));

            $results[] = $model;
        }

        return $results;
    }

    /**
     * @param array $where
     * @param array $order
     */
    protected function fetch(array $where = [], array $order = []): void
    {
        $fetched = Db::getInstance()->fetch(
            (new Query())
                ->from(static::tableName())
                ->where($where)
                ->orderBy($order)
        );
        if (!$fetched) {
            throw new RuntimeException('Empty query result for table ' . static::tableName());
        }

        $result = reset($fetched);

        $attributes = array_keys($this->attributes);

        foreach ($result as $key => $value) {
            if (in_array($key, $attributes, true)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @param string|int $id
     */
    protected function fetchByPk($id): void
    {
        $this->fetch([$this->getPkName() => $id]);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function save(array $attributes = [])
    {
        if ($this->getId() === null) {
            return $this->insert();
        }

        return $this->update($attributes);
    }

    private $preparedAttributes = [];

    /**
     * @param array $attributes
     */
    protected function prepareAttributes(array $attributes = []): void
    {
        foreach (array_keys($this->attributes) as $name) {
            if (!$attributes || in_array($name, $attributes, true)) {
                $this->preparedAttributes[$name] = $this->$name;
            }
        }
    }

    /**
     * @return bool
     */
    public function insert(): bool
    {
        $this->prepareAttributes();

        $id = Db::getInstance()->insert(static::tableName(), $this->preparedAttributes);

        if ($id === false) {
            return false;
        }

        $this->{$this->getPkName()} = $id;
        $this->setId($id);

        return true;
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function update(array $attributes): bool
    {
        $this->prepareAttributes($attributes);

        return Db::getInstance()->update(static::tableName(), $this->preparedAttributes, $this->getPkName(), $id = $this->getId());
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return Db::getInstance()->delete(static::tableName(), $this->getPkName(), $id = $this->getId());
    }

    protected $_id;

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param int|string $value
     */
    public function setId($value): void
    {
        $this->_id = $value;
    }
}
