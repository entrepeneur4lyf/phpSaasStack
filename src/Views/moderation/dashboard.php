<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<h1>Content Moderation Dashboard</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Reported By</th>
            <th>Reported User</th>
            <th>Content Type</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reports as $report): ?>
        <tr>
            <td><?= $report['id'] ?></td>
            <td><?= $report['reporter_id'] ?></td>
            <td><?= $report['reported_user_id'] ?></td>
            <td><?= $report['content_type'] ?></td>
            <td><?= $report['reason'] ?></td>
            <td><?= $report['status'] ?></td>
            <td>
                <a href="<?= site_url('moderation/review/' . $report['id']) ?>">Review</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->endSection() ?>