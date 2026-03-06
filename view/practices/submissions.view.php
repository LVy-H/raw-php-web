<?php

use App\Core\View;

$practiceInfo = $practice ?? null;
$list = $submissions ?? [];
?>
<section class="stack">
    <div class="card">
        <h1 class="title" style="font-size: 28px;">Submissions</h1>
        <?php if ($practiceInfo !== null): ?>
            <p class="subtitle">
                Practice: <?= View::escape((string) ($practiceInfo['title'] ?? '')) ?>
            </p>
        <?php endif; ?>
        <div style="margin-top:14px;">
            <a class="pill" href="/practices">Back to practices</a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Student</th>
                <th>Username</th>
                <th>Submitted At</th>
                <th>File</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $submission): ?>
                <tr>
                    <td><?= View::escape((string) ($submission['student_name'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($submission['student_username'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($submission['submitted_at'] ?? '')) ?></td>
                    <td>
                        <a class="link" href="/submissions/<?= View::escape((string) ($submission['id'] ?? '')) ?>/download">
                            <?= View::escape((string) ($submission['file_name'] ?? 'Download')) ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
