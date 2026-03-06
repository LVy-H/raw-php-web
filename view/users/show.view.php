<?php

use App\Core\View;

$member = $user ?? null;
$isTeacher = (($authRole ?? null) === 'teacher');
$authId = (int) ($authUserId ?? 0);
?>
<section class="stack">
    <?php if ($member === null): ?>
        <div class="card">
            <h1 class="title" style="font-size: 28px;">User unavailable</h1>
            <p class="subtitle"><?= View::escape((string) ($forbiddenMessage ?? 'The requested user does not exist in this class workspace.')) ?></p>
            <a class="pill" href="/users">Back to directory</a>
        </div>
    <?php else: ?>
        <?php
        $memberId = (int) ($member['id'] ?? 0);
        $memberRole = (string) ($member['role'] ?? '');
        $canTeacherManage = $isTeacher && $memberRole === 'student';
        $canSelfEdit = !$isTeacher && $memberRole === 'student' && $memberId === $authId;
        ?>
        <div class="card">
            <h1 class="title" style="font-size: 28px;">@<?= View::escape((string) ($member['username'] ?? '')) ?></h1>
            <p class="subtitle">User ID: <?= View::escape((string) $memberId) ?></p>
            <p class="subtitle">Role: <?= View::escape($memberRole) ?></p>
            <p class="subtitle">Name: <?= View::escape((string) ($member['name'] ?? '')) ?></p>
            <p class="subtitle">Email: <?= View::escape((string) ($member['email'] ?? '')) ?></p>
            <p class="subtitle">Phone: <?= View::escape((string) ($member['phone'] ?? '')) ?></p>
            <div style="margin-top:14px;">
                <?php if ($canTeacherManage): ?>
                    <a class="pill" href="/users/<?= View::escape((string) $memberId) ?>/edit">Edit student</a>
                    <form action="/users/<?= View::escape((string) $memberId) ?>/delete" method="post" style="display:inline;margin:0 0 0 6px;">
                        <button type="submit" class="pill" style="cursor:pointer;">Delete student</button>
                    </form>
                <?php elseif ($canSelfEdit): ?>
                    <a class="pill" href="/users/<?= View::escape((string) $memberId) ?>/edit">Edit my account</a>
                <?php else: ?>
                    <span class="muted">Read-only access</span>
                <?php endif; ?>
                <a class="pill" href="/users">Back to directory</a>
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
