<?php

use App\Core\View;

$pageTitle = isset($title) ? (string) $title : 'Class Document Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($pageTitle) ?> · Class Document Manager</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<header class="nav">
    <div class="container nav-inner">
        <div class="brand">Class Document Manager</div>
        <nav class="menu">
            <a class="pill" href="/">Dashboard</a>
            <a class="pill" href="/users">Users</a>
            <a class="pill" href="/practices">Practices</a>
            <a class="pill" href="/games">Games</a>
            <?php if ($authUserId !== null): ?>
                <span class="muted"><?= View::escape($authName ?? $authUsername ?? 'user') ?> (<?= View::escape((string) ($authRole ?? 'user')) ?>)</span>
                <form action="/logout" method="post" style="margin:0;">
                    <?= \App\Core\Csrf::field() ?>
                    <button class="btn" type="submit">Sign out</button>
                </form>
            <?php else: ?>
                <a class="btn btn-primary" href="/login">Sign in</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <?= $content ?>
    </div>
</main>
</body>
</html>
