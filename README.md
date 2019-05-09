# LEAGUE

This is simple foosball league project.

## Implemented rules

- Every match needs 4 players divided into 2 teams: white and red.
- Every team contains of the defender and the attacker.
- Every player plays with and against every other player at all positions and sides.
- Every match ends when one of the teams (winner) scores 10 points.
- It's not possible for match to end with draw but it's possible for one of the teams to score no points (0).
- Each combinations of players, teams, and positions is unique in a season.
- If combinations of players, teams, and positions has already been played in the season the match is saved as next season match.

## Installation

1. Install League using Composer:
  
    `composer create-project --prefer-dist bizley/league league`
    
2. Prepare virtual host pointing to `/public` directory. Make sure server's URL rewrite engine is on.
3. Prepare DB of your choice. You can find DB structure in `/src/structure.sql` file. Modify the SQL according to your DB engine if necessary.
4. Insert all the players in DB table `player`. 
   Column `name` stores player's initials and must be unique, column `full` stores player's full name, 
   and column `season` stores number of first season player joined the league.
5. Modify the `/src/config.php` file.

## Stats

- Stats are calculated for a season.
- Player's place is set based on the average points.
- Best side and position are set based on the number of wins.
- Best and worst partners are counted based on the average points gained or lost in every match of the season.

## Next match

- Selected player with least games played in total is always suggested for next match at random position.
- The rest of the players are assigned positions randomly.
- Above two points are repeated until the drawn match has not been played before in the season.
- If all season combinations for available players have been already played next season match is drawn.
