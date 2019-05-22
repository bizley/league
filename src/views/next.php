<?php

use league\components\MatchForm;
use league\models\Player;

/**
 * @var $form MatchForm
 * @var $players Player[]
 * @var $url string
 */

?>
<div class="row">
    <div class="col-sm-3 text-center">
        <form method="post">
            <div class="form-group">
                <label for="availablePlayers">Select available players</label>
                <select class="custom-select" name="availablePlayers[]" id="availablePlayers" multiple size="7">
                    <?php $showAll = count($form->availablePlayers) === 0; foreach ($players as $player): ?>
                        <option value="<?= $player->name ?>" <?= $showAll || in_array($player->name, $form->availablePlayers, true) ? 'selected' : '' ?>><?= $player->full ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Suggest match</button>
        </form>
    </div>
    <div class="col">
        <?php if ($form->getError()): ?>
            <div class="alert alert-danger" role="alert">
                <?= $form->getError() ?>
            </div>
        <?php elseif ($form->availablePlayers): ?>
            <?php
            $possibleGames = $form->possibleGames();
            $next = $possibleGames['schema'];
            $season = $possibleGames['season'];
            ?>
            <div class="row mb-5">
                <div class="col-sm-5">
                    <div class="card bg-light">
                        <div class="card-header">White</div>
                        <div class="card-body">
                            <h5 class="card-title"><small>Defender</small> <?= $next['white.defender'] ?></h5>
                            <h5 class="card-title"><small>Attacker</small> <?= $next['white.attacker'] ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2 text-center">
                    <h1>VS</h1>
                    <p class="badge badge-primary">Season <?= $season ?></p>
                </div>
                <div class="col-sm-5">
                    <div class="card bg-danger">
                        <div class="card-header">Red</div>
                        <div class="card-body">
                            <h5 class="card-title"><small>Defender</small> <?= $next['red.defender'] ?></h5>
                            <h5 class="card-title"><small>Attacker</small> <?= $next['red.attacker'] ?></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5 text-center">
                    <a href="/add/white-<?= $next['white.attacker'] . '-' . $next['white.defender'] . '-' . $next['red.attacker'] . '-' . $next['red.defender'] ?>" class="btn btn-outline-secondary">
                        White won
                    </a>
                </div>
                <div class="col-sm-2"></div>
                <div class="col-sm-5 text-center">
                    <a href="/add/red-<?= $next['white.attacker'] . '-' . $next['white.defender'] . '-' . $next['red.attacker'] . '-' . $next['red.defender'] ?>" class="btn btn-outline-danger">
                        Red won
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 pt-5 pb-5 text-center">
                    Link to this match
                    <input type="text" class="nextLink form-control text-center" value="<?= $url . 'add/next' . $season . '-' . $next['white.attacker'] . '-' . $next['white.defender'] . '-' . $next['red.attacker'] . '-' . $next['red.defender'] ?>">
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
