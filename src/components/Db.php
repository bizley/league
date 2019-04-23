<?php

declare(strict_types=1);

namespace league\components;

use PDO;
use function array_keys;
use function implode;

/**
 * Class Db singleton
 * @package league\components
 */
final class Db
{
    private static $instance;

    private function __construct() {}

    private function __clone() {}

    /**
     * @return Db
     */
    public static function getInstance(): Db
    {
        if (static::$instance === null) {
            static::$instance = new Db();
        }

        return static::$instance;
    }

    private $connection;

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $parameters = require __DIR__ . '/../config.php';

            $dsn = $parameters['pdoDsn'] ?? null;
            $username = $parameters['dbUser'] ?? null;
            $password = $parameters['dbPassword'] ?? null;

            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return $this->connection;
    }

    /**
     * @param Query $query
     * @return array
     */
    public function fetch(Query $query): array
    {
        $statement = $this->getConnection()->prepare($query->statement());

        foreach ($query->getBindings() as $key => &$value) {
            $statement->bindParam($key, $value);
        }

        $result = [];

        if ($statement->execute()) {
            while ($row = $statement->fetch()) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * @param Query $query
     * @return int
     */
    public function count(Query $query): int
    {
        $statement = $this->getConnection()->prepare($query->select(['COUNT(*)'])->statement());

        foreach ($query->getBindings() as $key => &$value) {
            $statement->bindParam($key, $value);
        }

        $result = 0;

        if ($statement->execute()) {
            return (int) $statement->fetchColumn();
        }

        return $result;
    }

    /**
     * @param string $table
     * @param array $attributes
     * @return int|string|bool
     */
    public function insert(string $table, array $attributes)
    {
        $keys = array_keys($attributes);

        $query = "INSERT INTO `$table` (`" . implode('`,`', $keys) . '`) VALUES (:' . implode(',:', $keys) . ')';

        $statement = $this->getConnection()->prepare($query);

        foreach ($attributes as $key => &$value) {
            $statement->bindParam(':' . $key, $value);
        }

        if (!$statement->execute()) {
            return false;
        }

        return $this->getConnection()->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $attributes
     * @param string $pk
     * @param string|int $id
     * @return bool
     */
    public function update(string $table, array $attributes, string $pk, &$id): bool
    {
        $keys = array_keys($attributes);

        $query = "UPDATE `$table` SET ";

        foreach ($keys as $key) {
            $query .= "`$key` = :$key";
        }

        $query .= " WHERE `$pk` = :pk";

        $statement = $this->getConnection()->prepare($query);

        foreach ($attributes as $key => &$value) {
            $statement->bindParam(':' . $key, $value);
        }

        $statement->bindParam(':pk', $id);

        return $statement->execute();
    }

    /**
     * @param string $table
     * @param string $pk
     * @param string|int $id
     * @return bool
     */
    public function delete(string $table, string $pk, &$id): bool
    {
        $query = "DELETE FROM `$table` WHERE `$pk` = :pk";

        $statement = $this->getConnection()->prepare($query);

        $statement->bindParam(':pk', $id);

        return $statement->execute();
    }

    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getConnection()->commit();
    }

    public function rollBack(): void
    {
        $this->getConnection()->rollBack();
    }
}
