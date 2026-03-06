<?php

use App\Core\View;

$members = $users ?? [];
?>
<section class="stack">
    <div class="card">
        <h1 class="title" style="font-size: 28px;">Student Manager</h1>
        <p class="subtitle">Teachers can create, edit, and delete student accounts.</p>
        <div style="margin-top:14px;">
            <a class="btn btn-primary" href="/students/create">Create Student</a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Username</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><?= View::escape((string) ($member['id'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['name'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['email'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['phone'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['username'] ?? '')) ?></td>
                    <td>
                        <a class="link" href="/students/<?= View::escape((string) ($member['id'] ?? '')) ?>">View</a>
                        &nbsp;·&nbsp;
                        <a class="link" href="/students/<?= View::escape((string) ($member['id'] ?? '')) ?>/edit">Edit</a>
                        &nbsp;·&nbsp;
                        <form action="/students/<?= View::escape((string) ($member['id'] ?? '')) ?>/delete" method="post" style="display:inline;margin:0;">
                            <button type="submit" class="link" style="background:none;border:none;padding:0;cursor:pointer;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
