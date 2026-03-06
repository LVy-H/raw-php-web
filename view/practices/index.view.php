<?php

use App\Core\View;

$practiceList = $practices ?? [];
$isTeacher = (($authRole ?? null) === 'teacher');
$isStudent = (($authRole ?? null) === 'student');
$submissionMap = $studentSubmissions ?? [];
?>
<section class="stack">
    <div class="card">
        <h1 class="title" style="font-size: 28px;">Practice Files</h1>
        <?php if ($isTeacher): ?>
            <p class="subtitle">Upload practice files and review student submissions.</p>
            <div style="margin-top:14px;">
                <a class="btn btn-primary" href="/practices/create">Upload Practice</a>
            </div>
        <?php else: ?>
            <p class="subtitle">Download practice files and submit your work.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
            <?= View::escape((string) $successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert"><?= View::escape((string) $errorMessage) ?></div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>File</th>
                <th>Uploaded By</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($practiceList as $practice): ?>
                <?php $practiceId = (int) ($practice['id'] ?? 0); ?>
                <tr>
                    <td><?= View::escape((string) ($practice['title'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($practice['description'] ?? '')) ?></td>
                    <td>
                        <a class="link" href="/practices/<?= View::escape((string) $practiceId) ?>/download">
                            <?= View::escape((string) ($practice['file_name'] ?? 'Download')) ?>
                        </a>
                    </td>
                    <td><?= View::escape((string) ($practice['uploader_name'] ?? '')) ?></td>
                    <td>
                        <?php if ($isTeacher): ?>
                            <a class="link" href="/practices/<?= View::escape((string) $practiceId) ?>/submissions">View submissions</a>
                        <?php elseif ($isStudent): ?>
                            <form action="/practices/<?= View::escape((string) $practiceId) ?>/submit" method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <input class="input" type="file" name="submission_file" required style="max-width:180px;padding:6px;">
                                <button class="btn" type="submit">Submit</button>
                                <?php if (isset($submissionMap[$practiceId])): ?>
                                    <span class="muted">Submitted: <?= View::escape((string) ($submissionMap[$practiceId]['submitted_at'] ?? '')) ?></span>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <span class="muted">Read only</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
