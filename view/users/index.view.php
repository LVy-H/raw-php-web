<?php

use App\Core\View;

$members = $users ?? [];
?>
<section class="stack">
    <div class="card">
        <h1 class="title" style="font-size: 28px;">Class Members</h1>
        <p class="subtitle">A clean directory of users with quick profile access.</p>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Profile</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><?= View::escape((string) ($member['id'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($member['username'] ?? '')) ?></td>
                    <td>
                        <a class="link" href="/user/<?= View::escape((string) ($member['id'] ?? '')) ?>">Open</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
