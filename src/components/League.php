<?php declare(strict_types=1);

namespace league\components;

/**
 * Class League
 * @package league\components
 */
final class League
{
    /**
     * @return int|string
     */
    public static function currentSeason()
    {
        $parameters = require __DIR__ . '/../config.php';

        return $parameters['currentSeason'] ?? 1;
    }

    /**
     * @return bool
     */
    public function run()
    {
        if (!$this->isLogged()) {
            return (new Controller())->login();
        }

        return $this->routing();
    }

    /**
     * @return bool
     */
    public function isLogged(): bool
    {
        return isset($_SESSION['logged']) && $_SESSION['logged'];
    }

    /**
     * @return bool
     */
    public function routing(): bool
    {
        $request = $_SERVER['REQUEST_URI'];

        switch ($request) {
            case '/logout':
                return (new Controller())->logout();
            case '/add':
                return (new Controller())->add();
            case '/next':
                return (new Controller())->next();
            case '/stats':
                return (new Controller())->stats();
        }

        if (preg_match('/^\/seasons\/(\d+)\/?$/', $request, $matches)) {
            return (new Controller())->board((int) $matches[1]);
        }

        if (preg_match('/^\/stats\/(\d+)\/?$/', $request, $matches)) {
            return (new Controller())->stats((int) $matches[1]);
        }

        if (preg_match('/^\/add\/([\w\-]+)\/?$/', $request, $matches)) {
            return (new Controller())->add($matches[1]);
        }

        return (new Controller())->board();
    }
}
