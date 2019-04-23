<?php declare(strict_types=1);

namespace league\components;

use league\models\Match;
use league\models\Team;
use UnexpectedValueException;
use function array_keys;
use function array_rand;
use function count;
use function shuffle;

/**
 * Class NextMatch
 * @package league\components
 */
final class NextMatch
{
    /**
     * @var string[]
     */
    public $availablePlayers = [];

    private $players;

    public function __construct(array $players)
    {
        $this->players = $players;
    }

    /**
     * @return bool
     */
    public function load(): bool
    {
        if (!$_POST) {
            return false;
        }

        $this->availablePlayers = !empty($_POST['availablePlayers']) ? $_POST['availablePlayers'] : [];

        return true;
    }

    private $error;

    /**
     * @return null|string
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (count($this->availablePlayers) < 4) {
            $this->error = 'Select at least 4 players';

            return false;
        }

        foreach ($this->availablePlayers as $availablePlayer) {
            $found = false;

            foreach ($this->players as $player) {
                if ($player->name === $availablePlayer) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->error = 'Unknown player selected';

                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getPlayerWithLeastGames(): string
    {
        $leastGames = null;
        $playerToGo = null;

        foreach ($this->availablePlayers as $player) {
            $number = Db::getInstance()->count((new Query())->from(Team::tableName())->orWhere([
                'defender' => $player,
                'attacker' => $player,
            ]));

            if ($leastGames === null) {
                $leastGames = $number;
                $playerToGo = $player;
            }

            if ($leastGames > $number) {
                $playerToGo = $player;
                $leastGames = $number;
            }
        }

        return $playerToGo;
    }
    
    private $schema = [];

    public function resetSchema(): void
    {
        $this->schema = [
            'white.defender' => null,
            'white.attacker' => null,
            'red.defender' => null,
            'red.attacker' => null,
        ];
    }

    /**
     * @param string $playerWithLeastGames
     * @param array $matchPlayers
     */
    public function drawStartingSchema(string $playerWithLeastGames, array $matchPlayers): void
    {
        $team = array_rand(['white' => null, 'red' => null]);
        $position = array_rand(['defender' => null, 'attacker' => null]);

        shuffle($matchPlayers);

        $playerIndex = 0;

        $this->resetSchema();

        foreach ($this->schema as $schemaPosition => $player) {
            if ($schemaPosition === $team . '.' . $position) {
                $this->schema[$schemaPosition] = $playerWithLeastGames;
            } else {
                $this->schema[$schemaPosition] = $matchPlayers[$playerIndex++];
            }
        }
    }

    /**
     * @return array
     */
    public function possibleGames(): array
    {
        $playerWithLeastGames = $this->getPlayerWithLeastGames();

        $matchPlayers = [];
        foreach ($this->availablePlayers as $player) {
            if ($player !== $playerWithLeastGames) {
                $matchPlayers[] = $player;
            }
        }

        $season = League::currentSeason();

        $this->drawStartingSchema($playerWithLeastGames, $matchPlayers);

        if (!Db::getInstance()->fetch(
            (new Query())
                ->from(Match::tableName())
                ->where(array_merge($this->schema, ['season' => $season]))
                ->limit(1)
                ->join([
                    Team::tableName() . ' AS white' => 'white.id = match.white_team',
                    Team::tableName() . ' AS red' => 'red.id = match.red_team',
                ])
        )) {
            return [
                'schema' => $this->schema,
                'season' => $season,
            ];
        }

        return $this->drawSchema($playerWithLeastGames, $matchPlayers, $season);
    }

    /**
     * @return string
     * @throws UnexpectedValueException
     */
    public function getNextAvailablePosition(): string
    {
        $availablePosition = '';

        foreach (array_keys($this->schema) as $position) {
            if ($this->schema[$position] === null) {
                $availablePosition = $position;
                break;
            }
        }

        if ($availablePosition === '') {
            throw new UnexpectedValueException('Next available position can not be found!');
        }

        return $availablePosition;
    }

    /**
     * @param string $playerWithLeastGames
     * @param array $matchPlayers
     * @param int $season
     * @return array
     * @throws UnexpectedValueException
     */
    public function drawSchema(string $playerWithLeastGames, array $matchPlayers, int $season): array
    {
        $teams = ['white', 'red'];
        $positions = ['defender', 'attacker'];

        shuffle($teams);
        shuffle($positions);

        $nextMatch = false;
        $nextSeason = 1;

        while (!$nextMatch) {
            shuffle($matchPlayers);

            foreach ($teams as $team) {
                foreach ($positions as $position) {
                    $this->resetSchema();

                    // make sure player with least games always plays
                    $this->schema[$team . '.' . $position] = $playerWithLeastGames;

                    $position2 = $this->getNextAvailablePosition();

                    foreach ($matchPlayers as $player2) {
                        $this->schema[$position2] = $player2;

                        $position3 = $this->getNextAvailablePosition();

                        foreach ($matchPlayers as $player3) {
                            if ($player3 !== $player2) {
                                $this->schema[$position3] = $player3;

                                $position4 = $this->getNextAvailablePosition();

                                foreach ($matchPlayers as $player4) {
                                    if ($player4 !== $player3 && $player4 !== $player2) {
                                        $this->schema[$position4] = $player4;

                                        if (!Db::getInstance()->fetch(
                                            (new Query())
                                                ->from(Match::tableName())
                                                ->where(array_merge($this->schema, ['season' => $season]))
                                                ->limit(1)
                                                ->join([
                                                    Team::tableName() . ' AS white' => 'white.id = match.white_team',
                                                    Team::tableName() . ' AS red' => 'red.id = match.red_team',
                                                ])
                                        )) {
                                            $nextMatch = true;
                                            $nextSeason = $season;
                                            break 5;
                                        }

                                        $this->schema[$position4] = null;
                                    }
                                }
                                $this->schema[$position3] = null;
                            }
                        }
                        $this->schema[$position2] = null;
                    }
                }
            }

            $season++;
        }

        return [
            'schema' => $this->schema,
            'season' => $nextSeason,
        ];
    }
}
