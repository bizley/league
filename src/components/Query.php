<?php

declare(strict_types=1);

namespace league\components;

use function implode;
use function preg_split;
use function str_replace;
use function stripos;
use function strpos;
use function substr;

/**
 * Class Query
 * @package league\components
 */
final class Query
{
    private $from;

    /**
     * @param string $tableName
     * @return $this
     */
    public function from(string $tableName): self
    {
        $this->from = $tableName;

        return $this;
    }

    private $andWhere = [];

    /**
     * @param array $conditions
     * @return $this
     */
    public function where(array $conditions): self
    {
        $this->andWhere = $conditions;

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     */
    public function andWhere(array $conditions): self
    {
        return $this->where($conditions);
    }

    private $orWhere = [];

    /**
     * @param array $conditions
     * @return $this
     */
    public function orWhere(array $conditions): self
    {
        $this->orWhere = $conditions;

        return $this;
    }

    private $sort = [];

    /**
     * @param array $columns
     * @return $this
     */
    public function orderBy(array $columns): self
    {
        $this->sort = $columns;

        return $this;
    }

    private $limit;

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    private $relations = [];

    /**
     * @param array $relations
     * @return $this
     */
    public function join(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    private $select = [];

    /**
     * @param array $columns
     * @return $this
     */
    public function select(array $columns): self
    {
        $this->select = $columns;

        return $this;
    }

    private $bindings = [];

    /**
     * @param string $value
     * @return string
     */
    public function ticks(string $value): string
    {
        if (stripos($value, ' as ') !== false) {
            $name = substr($value, 0, strpos($value, ' '));
            $alias = substr($value, strrpos($value, ' ') + 1);
        } else {
            $name = $value;
            $alias = null;
        }

        if (strpos($name, '.') !== false) {
            $table = substr($name, 0, strpos($name, '.'));
            $column = substr($name, strrpos($name, '.') + 1);
            $name = "`$table`.`$column`";
        } else {
            $name = "`$name`";
        }

        if ($alias !== null) {
            return "$name  AS `$alias`";
        }

        return $name;
    }

    /**
     * @param string $value
     * @return string
     */
    public function onTicks(string $value): string
    {
        $parts = preg_split('/\s*\=\s*/', $value);

        foreach ($parts as &$part) {
            $part = $this->ticks($part);
        }

        return implode(' = ', $parts);
    }

    /**
     * @return string
     */
    public function statement(): string
    {
        $query = 'SELECT';

        if ($this->select) {
            $columns = [];
            foreach ($this->select as $column) {
                $columns[] = $column === 'COUNT(*)' ? $column : $this->ticks($column);
            }
            $query .= ' ' . implode(', ', $columns);
        } else {
            $query .= ' *';
        }

        $query .= ' FROM ' . $this->ticks($this->from);

        foreach ($this->relations as $joinTable => $on) {
            $query .= ' LEFT JOIN ' . $this->ticks($joinTable) . ' ON ' . $this->onTicks($on);
        }

        if ($this->andWhere) {
            $query .= ' WHERE ';

            $conditions = [];

            foreach ($this->andWhere as $key => $value) {
                $binding = ':' . str_replace('.', '', $key);
                $conditions[] = $this->ticks($key) . " = {$binding}";
                $this->bindings[$binding] = $value;
            }

            $query .= '(' . implode(' AND ', $conditions) . ')';
        }

        if ($this->orWhere) {
            if ($this->andWhere) {
                $query .= ' AND ';
            } else {
                $query .= ' WHERE ';
            }

            $conditions = [];

            foreach ($this->orWhere as $key => $value) {
                $binding = ':' . str_replace('.', '', $key);
                $conditions[] = $this->ticks($key) . " = {$binding}";
                $this->bindings[$binding] = $value;
            }

            $query .= '(' . implode(' OR ', $conditions) . ')';
        }

        if ($this->sort) {
            $query .= ' ORDER BY ';

            $sorting = [];

            foreach ($this->sort as $key => $sort) {
                $dir = 'ASC';
                if (strtolower($sort) === 'desc') {
                    $dir = 'DESC';
                }
                $sorting[] = $this->ticks($key) . " $dir";
            }

            $query .= implode(', ', $sorting);
        }

        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
