<?php

use App\Core\View;

$member = $user ?? null;
?>
<section class="stack">
    <?php if ($member === null): ?>
        <div class="card">
            <h1 class="title" style="font-size: 28px;">Member not found</h1>
            <p class="subtitle">The requested user does not exist in this class workspace.</p>
            <a class="pill" href="/users">Back to members</a>
        </div>
    <?php else: ?>
        <div class="card">
            <h1 class="title" style="font-size: 28px;">@<?= View::escape((string) ($member['username'] ?? '')) ?></h1>
            <p class="subtitle">Member ID: <?= View::escape((string) ($member['id'] ?? '')) ?></p>
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
