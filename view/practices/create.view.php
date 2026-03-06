<?php

use App\Core\View;

$formData = $form ?? ['title' => '', 'description' => ''];
?>
<section class="stack">
    <div class="card" style="max-width: 700px;">
        <h1 class="title" style="font-size: 28px;">Upload Practice</h1>
        <p class="subtitle">Teachers can publish practice files for students.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?= View::escape((string) $error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form" action="/practices" method="post" enctype="multipart/form-data" style="max-width:none;">
            <?= \App\Core\Csrf::field() ?>
            <label>
                <span class="muted">Title</span>
                <input class="input" type="text" name="title" value="<?= View::escape((string) ($formData['title'] ?? '')) ?>" required>
            </label>

            <label>
                <span class="muted">Description</span>
                <textarea class="input" name="description" rows="4" style="resize:vertical;"><?= View::escape((string) ($formData['description'] ?? '')) ?></textarea>
            </label>

            <label>
                <span class="muted">Practice file</span>
                <input class="input" type="file" name="practice_file" required>
            </label>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit">Upload</button>
                <a class="pill" href="/practices">Cancel</a>
            </div>
        </form>
    </div>
</section>
