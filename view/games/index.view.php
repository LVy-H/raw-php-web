<?php

use App\Core\View;

$isTeacher = (($authRole ?? null) === 'teacher');
$isStudent = (($authRole ?? null) === 'student');
$gameList = $games ?? [];
$rewardData = $reward ?? null;
?>
<section class="stack">
    <div class="card">
        <h1 class="title" style="font-size:28px;">Filename Guess Game</h1>
        <p class="subtitle">Teacher uploads a text file + hint. Students guess the original filename to unlock file content.</p>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
            <?= View::escape((string) $successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert"><?= View::escape((string) $errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($isTeacher): ?>
        <div class="card">
            <h2 style="margin-top:0;">Create Game</h2>
            <form class="form" action="/games" method="post" enctype="multipart/form-data" style="max-width:none;">
                <label>
                    <span class="muted">Hint</span>
                    <input class="input" type="text" name="hint" required>
                </label>
                <label>
                    <span class="muted">Reward file</span>
                    <input class="input" type="file" name="game_file" required>
                </label>
                <div><button class="btn btn-primary" type="submit">Upload Game</button></div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 style="margin-top:0;">Available Games</h2>
        <?php foreach ($gameList as $game): ?>
            <?php $id = (string) ($game['id'] ?? ''); ?>
            <div class="card" style="margin-top:10px;padding:14px;">
                <p style="margin:0;"><strong>Hint:</strong> <?= View::escape((string) ($game['hint'] ?? '')) ?></p>
                <?php if ($isTeacher): ?>
                    <p class="subtitle">Answer filename: <?= View::escape((string) ($game['answer_filename'] ?? '')) ?></p>
                <?php elseif ($isStudent): ?>
                    <form class="form" action="/games/<?= View::escape($id) ?>/guess" method="post" style="max-width:none;margin-top:8px;">
                        <label>
                            <span class="muted">Your guess (filename with extension)</span>
                            <input class="input" type="text" name="guess" placeholder="example.txt" required>
                        </label>
                        <div><button class="btn" type="submit">Submit Guess</button></div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (is_array($rewardData)): ?>
        <div class="card">
            <h2 style="margin-top:0;">Reward Content</h2>
            <p class="subtitle">Unlocked by correct guess: <?= View::escape((string) ($rewardData['answer'] ?? '')) ?></p>
            <pre style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:12px;white-space:pre-wrap;"><?= View::escape((string) ($rewardData['content'] ?? '')) ?></pre>
        </div>
    <?php endif; ?>
</section>
