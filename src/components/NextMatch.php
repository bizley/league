<?php declare(strict_types=1);

namespace league\components;

use league\models\Match;
use league\models\Team;
use UnexpectedValueException;
use function array_keys;
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

    /**
     * @var array
     */
    public $playersSeasons = [];

    /**
     * @var int
     */
    public $lowestPossibleSeason;

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
                    $this->playersSeasons[$availablePlayer] = $player->season;
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
     * @param array $matchPlayers
     */
    public function drawStartingSchema(array $matchPlayers): void
    {
        shuffle($matchPlayers);

        $playerIndex = 0;

        $this->resetSchema();

        $this->lowestPossibleSeason = 0;

        foreach ($this->schema as $schemaPosition => $player) {
            $nextPlayer = $matchPlayers[$playerIndex++];
            $this->schema[$schemaPosition] = $nextPlayer;
            if ($this->lowestPossibleSeason < $this->playersSeasons[$nextPlayer]) {
                $this->lowestPossibleSeason = $this->playersSeasons[$nextPlayer];
            }
        }
    }

    /**
     * @return array
     */
    public function possibleGames(): array
    {
        $this->drawStartingSchema($this->availablePlayers);

        if (!Db::getInstance()->fetch(
            (new Query())
                ->from(Match::tableName())
                ->where(array_merge($this->schema, ['season' => $this->lowestPossibleSeason]))
                ->limit(1)
                ->join([
                    Team::tableName() . ' AS white' => 'white.id = match.white_team',
                    Team::tableName() . ' AS red' => 'red.id = match.red_team',
                ])
        )) {
            return [
                'schema' => $this->schema,
                'season' => $this->lowestPossibleSeason,
            ];
        }

        return $this->drawSchema($this->availablePlayers);
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
     * @param array $matchPlayers
     * @return array
     * @throws UnexpectedValueException
     */
    public function drawSchema(array $matchPlayers): array
    {
        $season = League::currentSeason();

        $nextMatch = false;
        $nextSeason = 1;

        while (!$nextMatch) {
            shuffle($matchPlayers);

            $this->resetSchema();

            $this->lowestPossibleSeason = 0;
            $position1 = $this->getNextAvailablePosition();

            foreach ($matchPlayers as $player1) {
                $this->schema[$position1] = $player1;
                if ($this->lowestPossibleSeason < $this->playersSeasons[$player1]) {
                    $this->lowestPossibleSeason = $this->playersSeasons[$player1];
                }

                $position2 = $this->getNextAvailablePosition();

                foreach ($matchPlayers as $player2) {
                    if ($player2 !== $player1) {
                        $this->schema[$position2] = $player2;
                        if ($this->lowestPossibleSeason < $this->playersSeasons[$player2]) {
                            $this->lowestPossibleSeason = $this->playersSeasons[$player2];
                        }

                        $position3 = $this->getNextAvailablePosition();

                        foreach ($matchPlayers as $player3) {
                            if ($player3 !== $player2 && $player3 !== $player1) {
                                $this->schema[$position3] = $player3;
                                if ($this->lowestPossibleSeason < $this->playersSeasons[$player3]) {
                                    $this->lowestPossibleSeason = $this->playersSeasons[$player3];
                                }

                                $position4 = $this->getNextAvailablePosition();

                                foreach ($matchPlayers as $player4) {
                                    if ($player4 !== $player3 && $player4 !== $player2 && $player4 !== $player1) {
                                        $this->schema[$position4] = $player4;
                                        if ($this->lowestPossibleSeason < $this->playersSeasons[$player4]) {
                                            $this->lowestPossibleSeason = $this->playersSeasons[$player4];
                                        }

                                        $checkSeason = $season;
                                        if ($checkSeason < $this->lowestPossibleSeason) {
                                            $checkSeason = $this->lowestPossibleSeason;
                                        }

                                        if (!Db::getInstance()->fetch(
                                            (new Query())
                                                ->from(Match::tableName())
                                                ->where(array_merge($this->schema, ['season' => $checkSeason]))
                                                ->limit(1)
                                                ->join([
                                                    Team::tableName() . ' AS white' => 'white.id = match.white_team',
                                                    Team::tableName() . ' AS red' => 'red.id = match.red_team',
                                                ])
                                        )) {
                                            $nextSeason = $checkSeason;
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
                $this->schema[$position1] = null;
            }

            $season++;
        }

        return [
            'schema' => $this->schema,
            'season' => $nextSeason,
        ];
    }
}
