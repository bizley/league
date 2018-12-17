<?php declare(strict_types=1);

namespace league\models;

/**
 * Class Match
 * @package league\models
 */
final class Match extends Model
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'match';
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
        'white_team' => null,
        'red_team' => null,
        'white_score' => null,
        'red_score' => null,
        'season' => null,
        'date' => null,
    ];
}
