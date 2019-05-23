<?php

declare(strict_types=1);

namespace league\components;

use league\models\Match;
use league\models\Player;
use league\models\Team;
use function array_key_exists;
use function count;
use function header;
use function ob_get_clean;
use function ob_implicit_flush;
use function ob_start;
use function setcookie;
use function time;

/**
 * Class Controller
 * @package league\components
 */
final class Controller
{
    /**
     * @var string
     */
    public $layout = 'layout';

    /**
     * @return bool
     */
    public function logout(): bool
    {
        unset($_SESSION['logged']);

        return $this->login();
    }

    /**
     * @param string|null $view
     * @return bool
     */
    public function redirect(string $view = null): bool
    {
        header('Location: /' . ($view ?? ''));

        return true;
    }

    /**
     * @return bool
     */
    public function login(): bool
    {
        $parameters = require __DIR__ . '/../config.php';

        $boardPassword = $parameters['leaguePassword'] ?? '';

        if (isset($_POST['pass']) && $_POST['pass'] === $boardPassword) {
            $_SESSION['logged'] = true;

            return $this->redirect();
        }

        $this->layout = 'login-layout';

        return $this->render('login');
    }

    /**
     * @param int $season
     * @return bool
     */
    public function board(int $season = null): bool
    {
        $lastSeason = Match::find([], ['season' => 'desc']);

        $topSeason = 1;

        if ($lastSeason) {
            $topSeason = (int)$lastSeason->season;
        }

        if (($season === null) && array_key_exists('LeagueBoardSeason', $_COOKIE)) {
            $season = (int)$_COOKIE['LeagueBoardSeason'];
        }

        if ($season === null || $season > $topSeason) {
            $season = $topSeason;
        }

        setcookie('LeagueBoardSeason', (string)$season, time() + 30 * 24 * 60 * 60, '/');

        $matches = Db::getInstance()->fetch(
            (new Query())
                ->select([
                    'white.defender AS white_defender',
                    'white.attacker AS white_attacker',
                    'white_score',
                    'red.defender AS red_defender',
                    'red.attacker AS red_attacker',
                    'red_score',
                    'date'
                ])
                ->from(Match::tableName())
                ->where(['season' => $season])
                ->orderBy(['date' => 'desc'])
                ->join([
                    Team::tableName() . ' AS white' => 'white.id = match.white_team',
                    Team::tableName() . ' AS red' => 'red.id = match.red_team',
                ])
        );

        $players = Player::findAll(['<=', 'season', $season]);

        $playersLeft = count($players);
        $totalPossibleMatches = 1;
        $positions = 4;
        while ($positions-- > 0) {
            $totalPossibleMatches *= $playersLeft--;
        }

        return $this->render('board', [
            'topSeason' => $topSeason,
            'season' => $season,
            'matches' => $matches,
            'totalPossibleMatches' => $totalPossibleMatches,
            'players' => $players,
        ]);
    }

    /**
     * @param string|null $setup
     * @return bool
     */
    public function add(string $setup = null): bool
    {
        $players = Player::findAll();

        $form = new MatchForm($players, $setup);

        if ($form->load() && $form->validate() && $form->save()) {
            return $this->redirect();
        }

        return $this->render('add', [
            'menu' => 'add',
            'players' => $players,
            'form' => $form,
        ]);
    }

    /**
     * @param string|null $setup
     * @return bool
     */
    public function preview(string $setup = null): bool
    {
        $form = new MatchForm(Player::findAll(), $setup);
        $OGData = $form->generateOGData();

        $parameters = require __DIR__ . '/../config.php';

        return $this->render('preview', [
            'menu' => 'preview',
            'og' => [
                'url' => $parameters['leagueUrl'] . $OGData['link'],
                'title' => 'Next League Match',
                'site_name' => 'LEAGUE',
                'description' => $OGData['description'],
            ],
        ]);
    }

    /**
     * @return bool
     */
    public function next(): bool
    {
        $players = Player::findAll();

        $form = new NextMatch($players);

        if ($form->load()) {
            $form->validate();
        }

        $parameters = require __DIR__ . '/../config.php';

        return $this->render('next', [
            'menu' => 'next',
            'players' => $players,
            'form' => $form,
            'url' => $parameters['leagueUrl'],
        ]);
    }

    /**
     * @param int $season
     * @return bool
     */
    public function stats(int $season = null): bool
    {
        $lastSeason = Match::find([], ['season' => 'desc']);

        $topSeason = 1;

        if ($lastSeason) {
            $topSeason = (int)$lastSeason->season;
        }

        if (($season === null) && array_key_exists('LeagueStatsSeason', $_COOKIE)) {
            $season = (int)$_COOKIE['LeagueStatsSeason'];
        }

        if ($season === null || $season > $topSeason) {
            $season = $topSeason;
        }

        setcookie('LeagueStatsSeason', (string)$season, time() + 30 * 24 * 60 * 60, '/');

        $players = Db::getInstance()->count((new Query())->from(Player::tableName())->where(['<=', 'season', $season]));

        $playersLeft = $players;
        $totalPossibleMatches = 1;
        $positions = 4;
        while ($positions-- > 0) {
            $totalPossibleMatches *= $playersLeft--;
        }

        $playersLeft = $players - 1;
        $onePlayerLessPossibleMatches = 1;
        $positions = 4;
        while ($positions-- > 0) {
            $onePlayerLessPossibleMatches *= $playersLeft--;
        }

        return $this->render('stats', [
            'menu' => 'stats',
            'topSeason' => $topSeason,
            'season' => $season,
            'stats' => new Stats($season),
            'totalPossibleMatches' => $totalPossibleMatches - $onePlayerLessPossibleMatches,
        ]);
    }

    /**
     * @param string $view
     * @param array $params
     * @return bool
     */
    public function render(string $view, array $params = []): bool
    {
        $menu = null;
        $og = null;

        if (array_key_exists('menu', $params)) {
            $menu = $params['menu'];
        }
        if (array_key_exists('og', $params)) {
            $og = $params['og'];
        }

        $content = $this->renderFile($view, $params);

        require __DIR__ . '/../views/' . $this->layout . '.php';

        return true;
    }

    /**
     * @param string $view
     * @param array $params
     * @return false|string
     */
    public function renderFile(string $view, array $params = [])
    {
        ob_start();
        ob_implicit_flush(0);

        extract($params, EXTR_OVERWRITE);

        require __DIR__ . "/../views/$view.php";

        return ob_get_clean();
    }
}
