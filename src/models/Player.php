<?php

declare(strict_types=1);

namespace league\models;

/**
 * Class Player
 * @package league\models
 */
final class Player extends Model
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'player';
    }

    /**
     * @return string
     */
    public function getPkName(): string
    {
        return 'name';
    }

    /**
     * @return array
     */
    protected $attributes = [
        'name' => null,
        'full' => null,
    ];
}
