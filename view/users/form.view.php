<?php

use App\Core\View;

$mode = (string) ($mode ?? 'create');
$isEdit = $mode !== 'create';
$isSelfEdit = $mode === 'edit-self';
$action = $isEdit
    ? '/users/' . View::escape((string) ($studentId ?? '')) . '/update'
    : '/users';
?>
<section class="stack">
    <div class="card" style="max-width: 620px;">
        <h1 class="title" style="font-size: 28px;"><?= $isEdit ? ($isSelfEdit ? 'Edit My Account' : 'Edit Student') : 'Create Student' ?></h1>
        <p class="subtitle">
            <?= $isSelfEdit
                ? 'You can update your email, phone, and password. Name and username are locked.'
                : 'Manage student account information for your class.' ?>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?= View::escape((string) $error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form" action="<?= $action ?>" method="post" style="max-width:none;">
            <label>
                <span class="muted">Full name</span>
                <input class="input" type="text" name="name" value="<?= View::escape((string) ($form['name'] ?? '')) ?>" <?= $isSelfEdit ? 'readonly' : 'required' ?>>
            </label>

            <label>
                <span class="muted">Email</span>
                <input class="input" type="email" name="email" value="<?= View::escape((string) ($form['email'] ?? '')) ?>" required>
            </label>

            <label>
                <span class="muted">Phone</span>
                <input class="input" type="text" name="phone" value="<?= View::escape((string) ($form['phone'] ?? '')) ?>" required>
            </label>

            <label>
                <span class="muted">Username</span>
                <input class="input" type="text" name="username" value="<?= View::escape((string) ($form['username'] ?? '')) ?>" <?= $isSelfEdit ? 'readonly' : 'required' ?>>
            </label>

            <label>
                <span class="muted"><?= $isEdit ? 'Password (leave blank to keep current)' : 'Password' ?></span>
                <input class="input" type="password" name="password" <?= $isEdit ? '' : 'required' ?>>
            </label>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Changes' : 'Create Student' ?></button>
                <a class="pill" href="/users">Cancel</a>
            </div>
        </form>
    </div>
</section>
