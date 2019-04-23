<?php

declare(strict_types=1);

namespace league\models;

/**
 * Interface RepoInterface
 * @package league\models
 */
interface RepoInterface
{
    /**
     * @return string
     */
    public static function tableName(): string;

    /**
     * @return string
     */
    public function getPkName(): string;

    /**
     * @return int|string
     */
    public function getId();

    /**
     * @param int|string $value
     */
    public function setId($value): void;
}
