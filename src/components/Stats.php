<?php declare(strict_types=1);

namespace league\components;

use league\models\Match;
use league\models\Player;
use league\models\Team;

/**
 * Class Stats
 * @package league\components
 */
final class Stats
{
    private $season;

    public function __construct(int $season)
    {
        $this->season = $season;
    }

    /**
     * @param string $player
     * @param array $matches
     * @return int|float
     */
    public function countMedian(string $player, array $matches)
    {
        $total = count($matches);

        if ($total === 0) {
            return 0;
        }

        $playerScores = [];

        foreach ($matches as $match) {
            if ($match['white_defender'] === $player || $match['white_attacker'] === $player) {
                $playerScores[] = $match['white_score'];
            } else {
                $playerScores[] = $match['red_score'];
            }
        }

        sort($playerScores);

        if ($total % 2 > 0) {
            return $playerScores[(int) (($total - 1) / 2)];
        }

        return round(($playerScores[(int) ($total / 2) - 1] + $playerScores[(int) ($total / 2)]) / 2, 1);
    }

    /**
     * @param string $player
     * @param array $matches
     * @return array
     */
    public function countScore(string $player, array $matches): array
    {
        $total = count($matches);
        $score = 0;

        foreach ($matches as $match) {
            if ($match['white_defender'] === $player || $match['white_attacker'] === $player) {
                $score += $match['white_score'];
            } else {
                $score += $match['red_score'];
            }
        }

        return [
            'score' => $score,
            'average' => $total > 0 ? round($score / $total, 2) : 0,
            'median' => $this->countMedian($player, $matches),
            'total' => $total,
        ];
    }

    /**
     * @param string $player
     * @param array $matches
     * @return int
     */
    public function countWinRate(string $player, array $matches): int
    {
        $total = count($matches);
        $win = 0;

        foreach ($matches as $match) {
            if ($match['white_score'] > $match['red_score'] && ($match['white_defender'] === $player || $match['white_attacker'] === $player)) {
                $win++;
            } elseif ($match['white_score'] < $match['red_score'] && ($match['red_defender'] === $player || $match['red_attacker'] === $player)) {
                $win++;
            }
        }

        return $total > 0 ? (int) round($win * 100 / $total) : 0;
    }

    /**
     * @param string $player
     * @param array $matches
     * @return string
     */
    public function checkBestSide(string $player, array $matches): string
    {
        $white = 0;
        $red = 0;

        foreach ($matches as $match) {
            if ($match['white_score'] > $match['red_score'] && ($match['white_defender'] === $player || $match['white_attacker'] === $player)) {
                $white++;
            } elseif ($match['white_score'] < $match['red_score'] && ($match['red_defender'] === $player || $match['red_attacker'] === $player)) {
                $red++;
            }
        }

        return $white > $red ? 'white' : 'red';
    }

    /**
     * @param string $player
     * @param array $matches
     * @return array
     */
    public function scorePartners(string $player, array $matches): array
    {
        $partners = [];

        $best = '-';
        $worst = '-';

        foreach ($matches as $match) {
            $team = $match['white_defender'] === $player || $match['white_attacker'] === $player ? 'white' : 'red';
            $won = $match['white_score'] > $match['red_score'] ? 'white' : 'red';
            $points = abs($match['white_score'] - $match['red_score']);
            $partner = $match[$team . '_defender'] === $player ? $match[$team . '_attacker'] : $match[$team . '_defender'];

            if (!array_key_exists($partner, $partners)) {
                $partners[$partner] = [
                    'points' => 0,
                    'matches' => 0,
                ];
            }

            $partners[$partner]['matches'] += 1;

            if ($team === $won) {
                $partners[$partner]['points'] += $points;
            } else {
                $partners[$partner]['points'] -= $points;
            }
        }

        if ($partners) {
            $scoring = [];

            foreach (array_keys($partners) as $partner) {
                $scoring[$partner] = round($partners[$partner]['points'] / $partners[$partner]['matches'], 5);
            }

            arsort($scoring);

            foreach (array_keys($scoring) as $partner) {
                if ($best === '-') {
                    $best = $partner;
                }
                $worst = $partner;
            }
        }

        return [
            'best' => $best,
            'worst' => $worst,
        ];
    }

    /**
     * @param string $player
     * @param array $matches
     * @return string
     */
    public function checkBestPosition(string $player, array $matches): string
    {
        $defender = 0;
        $attacker = 0;

        foreach ($matches as $match) {
            if ($match['white_score'] > $match['red_score'] && ($match['white_defender'] === $player || $match['white_attacker'] === $player)) {
                if ($match['white_defender'] === $player) {
                    $defender++;
                } else {
                    $attacker++;
                }
            } elseif ($match['white_score'] < $match['red_score'] && ($match['red_defender'] === $player || $match['red_attacker'] === $player)) {
                if ($match['red_defender'] === $player) {
                    $defender++;
                } else {
                    $attacker++;
                }
            }
        }

        return $defender > $attacker ? 'defender' : 'attacker';
    }

    /**
     * @return array
     */
    public function getStats(): array
    {
        $players = Player::findAll();

        $stats = [];

        foreach ($players as $player) {
            $playerStats = [
                'name' => $player->name,
                'full' => $player->full,
            ];

            $matches = Db::getInstance()->fetch(
                (new Query())
                    ->select([
                        'white.defender AS white_defender',
                        'white.attacker AS white_attacker',
                        'white_score',
                        'red.defender AS red_defender',
                        'red.attacker AS red_attacker',
                        'red_score',
                    ])
                    ->from(Match::tableName())
                    ->where(['season' => $this->season])
                    ->orWhere([
                        'white.defender' => $player->name,
                        'white.attacker' => $player->name,
                        'red.defender' => $player->name,
                        'red.attacker' => $player->name,
                    ])
                    ->join([
                        Team::tableName() . ' AS white' => 'white.id = match.white_team',
                        Team::tableName() . ' AS red' => 'red.id = match.red_team',
                    ])
            );

            if (!$matches) {
                continue;
            }

            $playerStats['score'] = $this->countScore($player->name, $matches);
            $playerStats['rate'] = $this->countWinRate($player->name, $matches);
            $playerStats['side'] = $this->checkBestSide($player->name, $matches);
            $playerStats['position'] = $this->checkBestPosition($player->name, $matches);

            $partners = $this->scorePartners($player->name, $matches);

            $playerStats['best-partner'] = $partners['best'];
            $playerStats['worst-partner'] = $partners['worst'];

            $stats[] = $playerStats;
        }

        uasort($stats, function ($a, $b) {
            return $b['score']['average'] <=> $a['score']['average'];
        });

        return $stats;
    }
}
