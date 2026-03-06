<?php

use App\Core\View;

$username = (string) (($form['username'] ?? ''));
?>
<section class="stack">
    <div class="card" style="max-width: 520px;">
        <h1 class="title" style="font-size: 28px;">Sign in</h1>
        <p class="subtitle">Enter your class account to access shared member documents.</p>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= View::escape((string) $error) ?></div>
        <?php endif; ?>

        <form class="form" action="/login" method="post">
            <label>
                <span class="muted">Username</span>
                <input class="input" type="text" name="username" value="<?= View::escape($username) ?>" autocomplete="username" required>
            </label>
            <label>
                <span class="muted">Password</span>
                <input class="input" type="password" name="password" autocomplete="current-password" required>
            </label>
            <button class="btn btn-primary" type="submit">Continue</button>
        </form>
    </div>
</section>
