<?php

/**
 * This is League configuration.
 * Keep array keys as-is and change array values.
 *
 * 'pdoDsn' key is the $dsn parameter for the PDO object construct
 * https://secure.php.net/manual/en/pdo.construct.php
 */

return [
    'leaguePassword' => 'enter league board password here', // this password to be provided to "log in"
    'currentSeason' => 1, // change when all matches of the current season have been played to avoid unnecessary steps when looking for next match
    'pdoDsn' => 'mysql:host=localhost;dbname=liga', // see description above
    'dbUser' => 'root', // database user name if required
    'dbPassword' => 'root', // database password if required
    'officeIP' => '127.0.0.1', // office IP to skip login
    'leagueUrl' => 'https://league.com/', // league URL with trailing slash
];
