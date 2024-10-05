<div x-data="posts()" class="posts-container">
    <h1>Posts</h1>

    <sl-button @click="showCreatePostModal = true">Create New Post</sl-button>

    <div class="posts-list">
        <template x-for="post in posts" :key="post.id">
            <sl-card class="post-card">
                <h2 slot="header" x-text="post.title"></h2>
                <div x-html="renderMarkdown(post.content)"></div>
                <div class="post-meta">
                    <span x-text="'Posted on: ' + formatDate(post.created_at)"></span>
                    <span x-text="'Likes: ' + post.likes_count"></span>
                    <span x-text="'Comments: ' + post.comments_count"></span>
                </div>
                <div slot="footer">
                    <sl-button @click="editPost(post)">Edit</sl-button>
                    <sl-button @click="deletePost(post.id)" variant="danger">Delete</sl-button>
                </div>
            </sl-card>
        </template>
    </div>

    <sl-dialog label="Create New Post" :open="showCreatePostModal">
        <sl-form @submit="createPost">
            <sl-input name="title" label="Title" required></sl-input>
            <div id="create-post-editor"></div>
            <asset-library @asset-selected="insertAsset"></asset-library>
            <sl-button type="submit" variant="primary">Create Post</sl-button>
            <sl-button @click="showCreatePostModal = false" variant="default">Cancel</sl-button>
        </sl-form>
    </sl-dialog>

    <sl-dialog label="Edit Post" :open="showEditPostModal">
        <sl-form @submit="updatePost">
            <sl-input name="title" label="Title" x-model="editingPost.title" required></sl-input>
            <div id="edit-post-editor"></div>
            <asset-library @asset-selected="insertAsset"></asset-library>
            <sl-button type="submit" variant="primary">Update Post</sl-button>
            <sl-button @click="showEditPostModal = false" variant="default">Cancel</sl-button>
        </sl-form>
    </sl-dialog>
</div>

<script>
function posts() {
    let createEditor, editEditor;

    return {
        posts: <?= json_encode($posts) ?>,
        showCreatePostModal: false,
        showEditPostModal: false,
        editingPost: null,

        init() {
            this.$watch('showCreatePostModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        createEditor = new TinyMDE.Editor({
                            element: 'create-post-editor',
                            content: ''
                        });
                    });
                }
            });

            this.$watch('showEditPostModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        editEditor = new TinyMDE.Editor({
                            element: 'edit-post-editor',
                            content: this.editingPost.content
                        });
                    });
                }
            });
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        },

        async renderMarkdown(content) {
            const response = await fetch('/api/render-markdown', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ markdown: content })
            });

            if (response.ok) {
                const result = await response.json();
                // Create a temporary div to hold the HTML content
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = result.html;

                // Handle embeds
                const embeds = tempDiv.querySelectorAll('.embed');
                embeds.forEach(embed => {
                    const iframe = embed.querySelector('iframe');
                    if (iframe) {
                        // Add sandbox attribute to iframes for security
                        iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-popups');
                        // Optionally, you can add more attributes or modify existing ones
                        iframe.setAttribute('loading', 'lazy');
                    }
                });

                return tempDiv.innerHTML;
            } else {
                console.error('Failed to render markdown');
                return content; // Return raw content if rendering fails
            }
        },

        insertAsset(asset) {
            const assetMarkdown = `![${asset.name}](${asset.url})`;
            if (this.showEditPostModal) {
                editEditor.insertText(assetMarkdown);
            } else {
                createEditor.insertText(assetMarkdown);
            }
        },

        async createPost(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('content', createEditor.getContent());

            const response = await fetch('/posts/create', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const newPost = await response.json();
                this.posts.unshift(newPost);
                this.showCreatePostModal = false;
                form.reset();
            } else {
                alert('Failed to create post');
            }
        },

        editPost(post) {
            this.editingPost = { ...post };
            this.showEditPostModal = true;
        },

        async updatePost(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('id', this.editingPost.id);
            formData.append('content', editEditor.getContent());

            const response = await fetch(`/posts/update/${this.editingPost.id}`, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const updatedPost = await response.json();
                const index = this.posts.findIndex(p => p.id === updatedPost.id);
                this.posts[index] = updatedPost;
                this.showEditPostModal = false;
                this.editingPost = null;
            } else {
                alert('Failed to update post');
            }
        },

        async deletePost(postId) {
            if (confirm('Are you sure you want to delete this post?')) {
                const response = await fetch(`/posts/delete/${postId}`, {
                    method: 'POST'
                });

                if (response.ok) {
                    this.posts = this.posts.filter(p => p.id !== postId);
                } else {
                    alert('Failed to delete post');
                }
            }
        }
    }
}
</script>