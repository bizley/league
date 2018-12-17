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
    'pdoDsn' => 'mysql:host=localhost;dbname=league', // see description above
    'dbUser' => null, // database user name if required
    'dbPassword' => null, // database password if required
];
