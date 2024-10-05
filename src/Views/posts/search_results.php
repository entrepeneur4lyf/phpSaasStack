<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="search-results">
    <h1>Search Results for "<?= esc($query) ?>"</h1>

    <?php if (empty($posts)): ?>
        <p>No results found.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <h2><a href="<?= site_url('posts/view/' . $post['id']) ?>"><?= esc($post['title']) ?></a></h2>
                <p><?= character_limiter(strip_tags($post['content']), 200) ?></p>
                <div class="post-meta">
                    <span>Posted on: <?= $post['created_at'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>

        <?= $pager->links() ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>