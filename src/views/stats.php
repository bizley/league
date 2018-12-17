<div class="row">
    <div class="col-sm-2">
        <ul class="nav flex-sm-column">
            <?php for ($i = $topSeason; $i > 0; $i--): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $i === $season ? 'disabled' : '' ?>" href="/stats/<?= $i ?>">Season <?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="col">
        <?php $playerStats = $stats->getStats();
        foreach ($playerStats as $playerStat): $percent = round($playerStat['score']['total'] * 100 / $totalPossibleMatches); ?>
            <div class="card float-left mb-3 mr-3" style="width: 18rem;">
                <div class="card-header"><?= $playerStat['full'] ?></div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Points / Matches
                        <div class="float-right">
                            <span class="badge badge-primary"><?= $playerStat['score']['score'] ?></span> /
                            <span class="badge badge-primary"><?= $playerStat['score']['total'] ?></span>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Average / Median
                        <div class="float-right">
                            <span class="badge badge-primary"><?= $playerStat['score']['average'] ?></span> /
                            <span class="badge badge-primary"><?= $playerStat['score']['median'] ?></span>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Wins<span class="badge badge-success"><?= $playerStat['rate'] ?>%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Best side<span class="badge badge-<?= $playerStat['side'] === 'red' ? 'danger' : 'light' ?>"><?= $playerStat['side'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Best position<span class="badge badge-warning"><?= $playerStat['position'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Best partner<span class="badge badge-info"><?= $playerStat['best-partner'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Worst partner<span class="badge badge-info"><?= $playerStat['worst-partner'] ?></span>
                    </li>
                    <li class="list-group-item">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="<?= $percent ?>">
                                <?= $percent ?>%
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</div>
