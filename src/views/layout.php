<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <title>League</title>
</head>
<body>

<div class="m-4">
    <a href="/logout" class="btn btn-outline-secondary btn-sm float-right">Log out</a>
    <h1 class="text-center">LEAGUE</h1>
</div>

<div class="container mt-5 mb-5">
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $menu === null ? 'active' : '' ?>" href="/">Seasons</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $menu === 'stats' ? 'active' : '' ?>" href="/stats">Stats</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $menu === 'next' ? 'active' : '' ?>" href="/next">Next match</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $menu === 'add' ? 'active' : '' ?>" href="/add">Add score</a>
        </li>
    </ul>

    <?= $content ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<?php if ($menu === null): ?>
    <script src="/filter.js"></script>
<?php endif; ?>
</body>
</html>
