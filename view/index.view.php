<?php

use App\Core\View;

$totalDocuments = count($documents ?? []);
?>
<section class="stack">
    <div class="card">
        <h1 class="title">Simple, focused space for class documents</h1>
        <p class="subtitle">Organize lecture files, assignment briefs, and shared references with a clean minimal interface.</p>
    </div>

    <div class="grid-3">
        <article class="stat">
            <h3>Total Documents</h3>
            <p><?= View::escape((string) $totalDocuments) ?></p>
        </article>
        <article class="stat">
            <h3>Active Classes</h3>
            <p>3</p>
        </article>
        <article class="stat">
            <h3>Last Sync</h3>
            <p>Today</p>
        </article>
    </div>

    <div class="card">
        <h2 style="margin-top:0;">Latest Documents</h2>
        <table>
            <thead>
            <tr>
                <th>Document</th>
                <th>Class</th>
                <th>Updated</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (($documents ?? []) as $document): ?>
                <tr>
                    <td><?= View::escape((string) ($document['title'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($document['class'] ?? '')) ?></td>
                    <td><?= View::escape((string) ($document['updated_at'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>