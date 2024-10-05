<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div x-data="postView()" class="post-container">
    <h1><?= esc($post['title']) ?></h1>
    <div class="post-content">
        <?= $this->markdownParser->parse(esc($post['content'])) ?>
    </div>
    <div class="post-meta">
        <span>Views: <?= $post['views'] ?></span>
        <span>Likes: <?= $post['likes'] ?></span>
        <span>Comments: <?= count($comments) ?></span>
        <span>Status: <?= ucfirst($post['status']) ?></span>
        <?php if ($post['status'] === 'scheduled'): ?>
            <span>Scheduled for: <?= $post['scheduled_at'] ?></span>
        <?php endif; ?>
    </div>

    <?php if (auth()->user()->can('edit_post', $post)): ?>
        <div class="post-actions">
            <a href="<?= site_url('posts/edit/' . $post['id']) ?>" class="btn btn-primary">Edit Post</a>
            
            <form action="<?= site_url('posts/schedule/' . $post['id']) ?>" method="post" class="inline-form">
                <input type="datetime-local" name="scheduled_at" required>
                <button type="submit" class="btn btn-secondary">Schedule Post</button>
            </form>

            <form action="<?= site_url('posts/toggle-featured/' . $post['id']) ?>" method="post" class="inline-form">
                <button type="submit" class="btn btn-secondary">
                    <?= $post['is_featured'] ? 'Unfeature Post' : 'Feature Post' ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <h2>Comments</h2>
    <div class="comment-form">
        <textarea x-model="newComment" placeholder="Write a comment..."></textarea>
        <button @click="addComment(null)">Add Comment</button>
    </div>

    <div class="comments-container">
        <template x-for="comment in comments" :key="comment.id">
            <div class="comment" :class="{ 'comment-reply': comment.parent_id !== null }">
                <div x-html="renderMarkdown(comment.content)"></div>
                <div class="comment-meta">
                    <span x-text="formatDate(comment.created_at)"></span>
                    <button @click="toggleReplyForm(comment.id)">Reply</button>
                    <button @click="upvoteComment(comment.id)">Upvote</button>
                    <button @click="downvoteComment(comment.id)">Downvote</button>
                </div>
                <div x-show="replyingTo === comment.id" class="reply-form">
                    <textarea x-model="replyContent" placeholder="Write a reply..."></textarea>
                    <button @click="addComment(comment.id)">Add Reply</button>
                </div>
                <div class="comment-replies">
                    <template x-for="reply in comment.replies" :key="reply.id">
                        <div class="comment comment-reply">
                            <div x-html="renderMarkdown(reply.content)"></div>
                            <div class="comment-meta">
                                <span x-text="formatDate(reply.created_at)"></span>
                                <button @click="toggleReplyForm(reply.id)">Reply</button>
                                <button @click="upvoteComment(reply.id)">Upvote</button>
                                <button @click="downvoteComment(reply.id)">Downvote</button>
                            </div>
                            <div x-show="replyingTo === reply.id" class="reply-form">
                                <textarea x-model="replyContent" placeholder="Write a reply..."></textarea>
                                <button @click="addComment(reply.id)">Add Reply</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function postView() {
    return {
        comments: <?= json_encode($comments) ?>,
        newComment: '',
        replyContent: '',
        replyingTo: null,

        addComment(parentId) {
            const content = parentId === null ? this.newComment : this.replyContent;
            fetch('/comments/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: <?= $post['id'] ?>,
                    parent_id: parentId,
                    content: content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.comments = this.updateComments(this.comments, {
                        id: data.commentId,
                        parent_id: parentId,
                        content: content,
                        created_at: new Date().toISOString(),
                        replies: []
                    });
                    this.newComment = '';
                    this.replyContent = '';
                    this.replyingTo = null;
                }
            });
        },

        updateComments(comments, newComment) {
            if (newComment.parent_id === null) {
                return [...comments, newComment];
            }

            return comments.map(comment => {
                if (comment.id === newComment.parent_id) {
                    return {...comment, replies: [...comment.replies, newComment]};
                }
                if (comment.replies) {
                    return {...comment, replies: this.updateComments(comment.replies, newComment)};
                }
                return comment;
            });
        },

        toggleReplyForm(commentId) {
            this.replyingTo = this.replyingTo === commentId ? null : commentId;
        },

        upvoteComment(commentId) {
            this.voteComment(commentId, 'up');
        },

        downvoteComment(commentId) {
            this.voteComment(commentId, 'down');
        },

        voteComment(commentId, voteType) {
            fetch(`/comments/vote/${commentId}/${voteType}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.comments = this.updateCommentVotes(this.comments, commentId, voteType, data.newCount);
                }
            });
        },

        updateCommentVotes(comments, commentId, voteType, newCount) {
            return comments.map(comment => {
                if (comment.id === commentId) {
                    return {...comment, [voteType + 'votes']: newCount};
                }
                if (comment.replies) {
                    return {...comment, replies: this.updateCommentVotes(comment.replies, commentId, voteType, newCount)};
                }
                return comment;
            });
        },

        renderMarkdown(content) {
            // Use the server-side markdown rendering here
            return content; // Placeholder for now
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }
    }
}
</script>
<?= $this->endSection() ?>