<?php

use App\Core\View;

$member = $user ?? null;
?>
<section class="stack">
    <?php if ($member === null): ?>
        <div class="card">
            <h1 class="title" style="font-size: 28px;">Student not found</h1>
            <p class="subtitle">The requested student does not exist in this class workspace.</p>
            <a class="pill" href="/students">Back to students</a>
        </div>
    <?php else: ?>
        <div class="card">
            <h1 class="title" style="font-size: 28px;">@<?= View::escape((string) ($member['username'] ?? '')) ?></h1>
            <p class="subtitle">Student ID: <?= View::escape((string) ($member['id'] ?? '')) ?></p>
            <p class="subtitle">Name: <?= View::escape((string) ($member['name'] ?? '')) ?></p>
            <p class="subtitle">Email: <?= View::escape((string) ($member['email'] ?? '')) ?></p>
            <p class="subtitle">Phone: <?= View::escape((string) ($member['phone'] ?? '')) ?></p>
            <div style="margin-top:14px;">
                <a class="pill" href="/students/<?= View::escape((string) ($member['id'] ?? '')) ?>/edit">Edit student</a>
                <a class="pill" href="/students">Back to students</a>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-top:0;">Recent Documents</h2>
            <table>
                <thead>
                <tr>
                    <th>Document</th>
                    <th>Updated</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($documents ?? []) as $document): ?>
                    <tr>
                        <td><?= View::escape((string) ($document['name'] ?? '')) ?></td>
                        <td><?= View::escape((string) ($document['updated_at'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
