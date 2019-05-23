<?php

/* @var $menu string */
/* @var $og array */
/* @var $content string */

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>League</title>
<?php if ($og): ?>
    <meta property="og:url" content="<?= $og['url'] ?>">
    <meta property="og:title" content="<?= $og['title'] ?>">
    <meta property="og:site_name" content="<?= $og['site_name'] ?>">
    <meta property="og:description" content="<?= $og['description'] ?>">
<?php endif; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<?php if ($menu === null): ?>
    <script src="/filter.js"></script>
<?php elseif ($menu === 'next'): ?>
    <script>
        jQuery(function ($) {
            $(".nextLink").on("click", function () {
                $(this).select();
            });
        });
    </script>
<?php endif; ?>
</body>
</html>
