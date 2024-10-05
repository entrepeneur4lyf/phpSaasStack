<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="featured-posts">
    <h1>Featured Posts</h1>

    <?php if (empty($posts)): ?>
        <p>No featured posts available.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-card featured">
                <h2><a href="<?= site_url('posts/view/' . $post['id']) ?>"><?= esc($post['title']) ?></a></h2>
                <p><?= character_limiter(strip_tags($post['content']), 200) ?></p>
                <div class="post-meta">
                    <span>Posted on: <?= $post['created_at'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>