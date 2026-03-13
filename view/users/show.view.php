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
            <?php
            $avatarUrl = !empty($member['avatar']) 
                ? '/users/' . $memberId . '/avatar' 
                : 'about:blank';
            ?>

            <div style="margin-bottom: 16px;">
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="User Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
            </div>
            <h1 class="title" style="margin-top:0; font-size: 28px;">@<?= View::escape((string) ($member['username'] ?? '')) ?></h1>
            <p class="subtitle">User ID: <?= View::escape((string) $memberId) ?></p>
            <p class="subtitle">Role: <?= View::escape($memberRole) ?></p>
            <p class="subtitle">Name: <?= View::escape((string) ($member['name'] ?? '')) ?></p>
            <p class="subtitle">Email: <?= View::escape((string) ($member['email'] ?? '')) ?></p>
            <p class="subtitle">Phone: <?= View::escape((string) ($member['phone'] ?? '')) ?></p>
            <div style="margin-top:14px;">
                <?php if ($canTeacherManage): ?>
                    <a class="pill" href="/users/<?= View::escape((string) $memberId) ?>/edit">Edit student</a>
                    <form action="/users/<?= View::escape((string) $memberId) ?>/delete" method="post" style="display:inline;margin:0 0 0 6px;">
                        <?= \App\Core\Csrf::field() ?>
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
            <h2 style="margin-top:0;">Private Notes</h2>
            <p class="subtitle">Owner can read all notes on this profile. Writers can read and edit/delete only notes they wrote.</p>

            <?php if (!empty($successMessage)): ?>
                <div style="margin-top:10px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;border-radius:10px;padding:10px 12px;">
                    <?= View::escape((string) $successMessage) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert" style="margin-top:10px;"><?= View::escape((string) $errorMessage) ?></div>
            <?php endif; ?>

            <form class="form" action="/users/<?= View::escape((string) $memberId) ?>/notes" method="post" style="max-width:none;margin-top:12px;">
                <?= \App\Core\Csrf::field() ?>
                <label>
                    <span class="muted">Leave a note for this profile</span>
                    <textarea class="input" name="content" rows="3" style="resize:vertical;" required></textarea>
                </label>
                <div>
                    <button class="btn" type="submit">Save note</button>
                </div>
            </form>

            <?php foreach (($notes ?? []) as $note): ?>
                <?php
                $noteId = (int) ($note['id'] ?? 0);
                $writerId = (int) ($note['writer_user_id'] ?? 0);
                $isWriter = $writerId === $authId;
                ?>
                <div class="card" style="margin-top:12px;padding:14px;">
                    <p class="subtitle" style="margin:0;">
                        By <?= View::escape((string) ($note['writer_name'] ?? $note['writer_username'] ?? 'Unknown')) ?>
                        · Updated <?= View::escape((string) ($note['updated_at'] ?? '')) ?>
                    </p>

                    <?php if ($isWriter): ?>
                        <form class="form" action="/notes/<?= View::escape((string) $noteId) ?>/update" method="post" style="max-width:none;margin-top:8px;">
                            <?= \App\Core\Csrf::field() ?>
                            <textarea class="input" name="content" rows="3" style="resize:vertical;" required><?= View::escape((string) ($note['content'] ?? '')) ?></textarea>
                            <div style="display:flex;gap:8px;">
                                <button class="btn" type="submit">Update</button>
                                <button class="btn" type="submit" formaction="/notes/<?= View::escape((string) $noteId) ?>/delete">Delete</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p style="margin:8px 0 0;"><?= nl2br(View::escape((string) ($note['content'] ?? ''))) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
