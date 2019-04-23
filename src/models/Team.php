<?php

declare(strict_types=1);

namespace league\models;

/**
 * Class Team
 * @package league\models
 */
final class Team extends Model
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'team';
    }

    /**
     * @return string
     */
    public function getPkName(): string
    {
        return 'id';
    }

    /**
     * @return array
     */
    protected $attributes = [
        'id' => null,
        'defender' => null,
        'attacker' => null,
    ];
}
