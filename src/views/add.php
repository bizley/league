<?php

/* @var $form \league\components\MatchForm */

if ($form->getError()): ?>
    <div class="alert alert-danger" role="alert">
        <?= $form->getError() ?>
    </div>
<?php endif; ?>
<form method="post">
    <div class="row">
        <div class="col-sm-3 offset-sm-3">
            <h2 class="form-group">White</h2>
            <div class="form-group">
                <label for="whiteAtt">Attacker</label>
                <select class="custom-select" name="whiteAttacker" id="whiteAtt">
                    <option <?= $form->whiteAttacker === null ? 'selected' : '' ?>>Choose:</option>
                    <?php foreach ($players as $player): ?>
                        <option value="<?= $player->name ?>" <?= $form->whiteAttacker === $player->name ? 'selected' : '' ?>><?= $player->full ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="whiteDef">Defender</label>
                <select class="custom-select" name="whiteDefender" id="whiteDef">
                    <option <?= $form->whiteDefender === null ? 'selected' : '' ?>>Choose:</option>
                    <?php foreach ($players as $player): ?>
                        <option value="<?= $player->name ?>" <?= $form->whiteDefender === $player->name ? 'selected' : '' ?>><?= $player->full ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="whiteScore">Score</label>
                <input type="text" class="form-control" name="whiteScore" id="whiteScore" value="<?= $form->whiteScore ?? ($form->winningSide === 'white' ? 10 : '' ) ?>">
            </div>
        </div>

        <div class="col-sm-3">
            <h2 class="form-group">Red</h2>
            <div class="form-group">
                <label for="redAtt">Attacker</label>
                <select class="custom-select" name="redAttacker" id="redAtt">
                    <option <?= $form->redAttacker === null ? 'selected' : '' ?>>Choose:</option>
                    <?php foreach ($players as $player): ?>
                        <option value="<?= $player->name ?>" <?= $form->redAttacker === $player->name ? 'selected' : '' ?>><?= $player->full ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="redDef">Defender</label>
                <select class="custom-select" name="redDefender" id="redDef">
                    <option <?= $form->redDefender === null ? 'selected' : '' ?>>Choose:</option>
                    <?php foreach ($players as $player): ?>
                        <option value="<?= $player->name ?>" <?= $form->redDefender === $player->name ? 'selected' : '' ?>><?= $player->full ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="redScore">Score</label>
                <input type="text" class="form-control" name="redScore" id="redScore" value="<?= $form->redScore ?? ($form->winningSide === 'red' ? 10 : '' ) ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 offset-sm-3 form-group">
            <button type="submit" class="btn btn-primary btn-block">Save</button>
        </div>
    </div>
</form>