<?php

$matchesCount = count($matches);
$percent = round($matchesCount * 100 / $totalPossibleMatches);

?>
<div class="row">
    <div class="col-sm-2">
        <ul class="nav flex-sm-column mb-5">
            <?php for ($i = $topSeason; $i > 0; $i--): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $i === $season ? 'disabled' : '' ?>" href="/seasons/<?= $i ?>">Season <?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
        <ul class="list-unstyled">
            <li><a href="#" class="badge badge-success filter type" data-filter="and">all filters</a></li>
            <li><a href="#" class="badge badge-light filter type" data-filter="or">any filter</a></li>
        </ul>
        <ul class="list-unstyled">
            <?php foreach ($players as $player): ?>
                <li><a href="#" class="badge badge-light filter player" data-filter="<?= $player->name ?>"><?= $player->full ?></a></li>
            <?php endforeach; ?>
        </ul>
        <ul class="list-unstyled">
            <li><a href="#" class="badge badge-light filter team" data-filter="wh">white</a></li>
            <li><a href="#" class="badge badge-light filter team" data-filter="rd">red</a></li>
        </ul>
        <ul class="list-unstyled">
            <li><a href="#" class="badge badge-light filter position" data-filter="df">defender</a></li>
            <li><a href="#" class="badge badge-light filter position" data-filter="at">attacker</a></li>
        </ul>
    </div>
    <div class="col">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="<?= $percent ?>">
                <?= $percent ?>% (<?= $matchesCount ?> / <?= $totalPossibleMatches ?>)
            </div>
        </div>

        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col" colspan="3" class="text-center">White</th>
                <th scope="col" colspan="3" class="text-center">Red</th>
                <th scope="col"></th>
            </tr>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Defender</th>
                <th scope="col">Attacker</th>
                <th scope="col">Score</th>
                <th scope="col">Score</th>
                <th scope="col">Attacker</th>
                <th scope="col">Defender</th>
                <th scope="col">Date</th>
            </tr>
            </thead>
            <tbody>
                <?php $row = 1;
                foreach ($matches as $match): ?>
                    <tr class="match <?= $match['white_defender'] . ' wh' . $match['white_defender'] . ' df' . $match['white_defender']
                        . ' ' . $match['white_attacker'] . ' wh' . $match['white_attacker'] . ' at' . $match['white_attacker'] . ' '
                        . $match['red_attacker'] . ' rd' . $match['red_attacker'] . ' at' . $match['red_attacker'] . ' ' . $match['red_defender']
                        . ' rd' . $match['red_defender'] . ' df' . $match['red_defender'] ?>">
                        <th scope="row"><?= $row++; ?></th>
                        <td><?= $match['white_defender'] ?></td>
                        <td><?= $match['white_attacker'] ?></td>
                        <td><?= $match['white_score'] ?></td>
                        <td><?= $match['red_score'] ?></td>
                        <td><?= $match['red_attacker'] ?></td>
                        <td><?= $match['red_defender'] ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($match['date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
