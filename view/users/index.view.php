<?php

use App\Core\View;

$members = $users ?? [];
$isTeacher = (($authRole ?? null) === 'teacher');
$authId = (int) ($authUserId ?? 0);
?>
<section class="stack">
    <div class="card">
        <h1 class="title" style="font-size: 28px;">User Directory</h1>
        <?php if ($isTeacher): ?>
            <p class="subtitle">Teacher mode: view all users, fully manage student accounts.</p>
            <div style="margin-top:14px;">
                <a class="btn btn-primary" href="/users/create">Create Student</a>
            </div>
        <?php else: ?>
            <p class="subtitle">Student mode: view all users, edit your own email, phone, and password.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Avatar</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <?php
                $memberId = (int) ($member['id'] ?? 0);
                $memberRole = (string) ($member['role'] ?? '');
                $canTeacherManage = $isTeacher && $memberRole === 'student';
                $canSelfEdit = !$isTeacher && $memberId === $authId && $memberRole === 'student';
                ?>
                <tr>
                    <td><?= View::escape((string) $memberId) ?></td>
                    <td>
                        <?php
                        $avatarUrl = !empty($member['avatar']) 
                            ? '/users/' . $memberId . '/avatar' 
                            : 'about:blank';
                        ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; display: block;">
                    </td>
                    <td><?= View::escape((string) ($member['name'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['email'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['phone'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['username'] ?? '')) ?></td>
                    <td><?= View::escape($memberRole) ?></td>
                    <td>
                        <a class="link" href="/users/<?= View::escape((string) $memberId) ?>">View</a>
                        <?php if ($canTeacherManage): ?>
                            &nbsp;·&nbsp;
                            <a class="link" href="/users/<?= View::escape((string) $memberId) ?>/edit">Edit</a>
                            &nbsp;·&nbsp;
                            <form action="/users/<?= View::escape((string) $memberId) ?>/delete" method="post" style="display:inline;margin:0;">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="link" style="background:none;border:none;padding:0;cursor:pointer;">Delete</button>
                            </form>
                        <?php elseif ($canSelfEdit): ?>
                            &nbsp;·&nbsp;
                            <a class="link" href="/users/<?= View::escape((string) $memberId) ?>/edit">Edit me</a>
                        <?php else: ?>
                            &nbsp;·&nbsp;
                            <span class="muted">View only</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
