<?php declare(strict_types=1);

namespace league\components;

use league\models\Match;
use league\models\Team;

/**
 * Class MatchForm
 * @package league\components
 */
final class MatchForm
{
    /**
     * @var string
     */
    public $whiteAttacker;

    /**
     * @var string
     */
    public $whiteDefender;

    /**
     * @var int
     */
    public $whiteScore;

    /**
     * @var string
     */
    public $redAttacker;

    /**
     * @var string
     */
    public $redDefender;

    /**
     * @var int
     */
    public $redScore;

    /**
     * @var string
     */
    public $winningSide;

    private $players;

    /**
     * MatchForm constructor.
     * @param array $players
     * @param string|null $setup
     */
    public function __construct(array $players, string $setup = null)
    {
        $this->players = $players;

        if ($setup !== null) {
            $players = explode('-', $setup);

            $this->winningSide = !empty($players[0]) ? $players[0] : null;
            $this->whiteAttacker = !empty($players[1]) ? $players[1] : null;
            $this->whiteDefender = !empty($players[2]) ? $players[2] : null;
            $this->redAttacker = !empty($players[3]) ? $players[3] : null;
            $this->redDefender = !empty($players[4]) ? $players[4] : null;
        }
    }

    /**
     * @return bool
     */
    public function load(): bool
    {
        if (!$_POST) {
            return false;
        }

        $this->whiteAttacker = !empty($_POST['whiteAttacker']) ? $_POST['whiteAttacker'] : null;
        $this->whiteDefender = !empty($_POST['whiteDefender']) ? $_POST['whiteDefender'] : null;
        $this->whiteScore = $_POST['whiteScore'] !== null && $_POST['whiteScore'] !== '' ? $_POST['whiteScore'] : null;
        $this->redAttacker = !empty($_POST['redAttacker']) ? $_POST['redAttacker'] : null;
        $this->redDefender = !empty($_POST['redDefender']) ? $_POST['redDefender'] : null;
        $this->redScore = $_POST['redScore'] !== null && $_POST['redScore'] !== '' ? $_POST['redScore'] : null;

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
        if ($this->whiteAttacker === null
            || $this->whiteDefender === null
            || $this->whiteScore === null
            || $this->redAttacker === null
            || $this->redDefender === null
            || $this->redScore === null) {
            $this->error = 'Fill all the fields';

            return false;
        }

        $selectedPlayers = [];

        $playerFields = [
            'whiteAttacker' => 'white attacker',
            'whiteDefender' => 'white defender',
            'redAttacker' => 'red attacker',
            'redDefender' => 'red defender',
        ];

        foreach ($playerFields as $field => $desc) {
            $found = false;

            foreach ($this->players as $player) {
                if ($player->name === $this->$field) {
                    $found = true;
                    break;
                }
            }

            if (!$found || \in_array($this->$field, $selectedPlayers, true)) {
                $this->error = "Unknown player given at \"$desc\" position";

                return false;
            }

            $selectedPlayers[] = $this->$field;
        }

        if ($this->whiteScore < 0 || $this->whiteScore > 10) {
            $this->error = 'Wrong score given for white team';

            return false;
        }

        if ($this->redScore < 0 || $this->redScore > 10) {
            $this->error = 'Wrong score given for red team';

            return false;
        }

        if ($this->redScore < 10 && $this->whiteScore < 10) {
            $this->error = 'Partial score given';

            return false;
        }

        if ((int) $this->redScore === 10 && (int) $this->whiteScore === 10) {
            $this->error = 'Wrong score given';

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        Db::getInstance()->beginTransaction();

        try {
            $season = 1;

            $lastSimilarMatch = Db::getInstance()->fetch(
                (new Query())
                    ->from(Match::tableName())
                    ->where([
                        'white.defender' => $this->whiteDefender,
                        'white.attacker' => $this->whiteAttacker,
                        'red.defender' => $this->redDefender,
                        'red.attacker' => $this->redAttacker,
                    ])
                    ->orderBy(['season' => 'desc'])
                    ->limit(1)
                    ->join([
                        Team::tableName() . ' AS white' => 'white.id = match.white_team',
                        Team::tableName() . ' AS red' => 'red.id = match.red_team',
                    ])
            );
            if ($lastSimilarMatch) {
                $season = (int) $lastSimilarMatch[0]['season'] + 1;
            }

            $white = new Team();
            $white->defender = $this->whiteDefender;
            $white->attacker = $this->whiteAttacker;

            if (!$white->save()) {
                throw new \Exception('Error while saving white team');
            }

            $red = new Team();
            $red->defender = $this->redDefender;
            $red->attacker = $this->redAttacker;

            if (!$red->save()) {
                throw new \Exception('Error while saving red team');
            }

            $match = new Match();
            $match->white_team = $white->id;
            $match->red_team = $red->id;
            $match->white_score = $this->whiteScore;
            $match->red_score = $this->redScore;
            $match->season = $season;
            $match->date = date('c');

            if (!$match->save()) {
                throw new \Exception('Error while saving the match');
            }

            Db::getInstance()->commit();

            return true;

        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();

            Db::getInstance()->rollBack();

            return false;
        }
    }
}