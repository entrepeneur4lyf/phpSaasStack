<!DOCTYPE html>
<html lang="en" x-data="profileApp()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title x-text="`${user.username}'s Profile`"></title>
    <link rel="stylesheet" href="/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/dark.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-header-left">
                <template x-if="profile.avatar_url">
                    <img :src="profile.avatar_url" alt="Avatar" class="profile-avatar">
                </template>
                <h1 class="profile-username" x-text="user.username"></h1>
                <p class="profile-role" x-text="`${user.role.charAt(0).toUpperCase() + user.role.slice(1)}${user.seller_type ? ' - ' + user.seller_type : ''}`"></p>
                <p class="profile-bio" x-text="profile.bio"></p>
                <template x-if="!isOwnProfile">
                    <sl-button @click="followUser(user.id)" x-text="following ? 'Unfollow' : 'Follow'"></sl-button>
                </template>
            </div>
            <div class="profile-header-right">
                <h2>Accomplishments</h2>
                <div class="badge-container">
                    <template x-for="badge in badges" :key="badge.id">
                        <sl-tooltip :content="badge.badge_description">
                            <sl-badge variant="primary">
                                <sl-icon :name="badge.badge_icon"></sl-icon>
                                <span x-text="badge.badge_name"></span>
                            </sl-badge>
                        </sl-tooltip>
                    </template>
                </div>
            </div>
        </div>

        <div class="profile-details">
            <div class="profile-links">
                <template x-for="(url, platform) in profile.social_media" :key="platform">
                    <a x-show="url" :href="url" target="_blank" rel="noopener noreferrer" :class="`social-link ${platform}`">
                        <sl-icon :name="platform"></sl-icon>
                    </a>
                </template>
            </div>
            <div class="seller-stats">
                <p x-text="`Total Sales: ${user.total_sales}`"></p>
                <p x-text="`Average Rating: ${user.average_rating.toFixed(1)} / 5.0`"></p>
                <p x-text="`Member Since: ${new Date(user.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}`"></p>
            </div>
        </div>

        <div class="skills-container">
            <h3>Skills</h3>
            <div class="skills-list">
                <template x-for="skill in skills" :key="skill.id">
                    <sl-badge variant="neutral" x-text="skill.skill_name"></sl-badge>
                </template>
            </div>
        </div>

        <div class="offered-categories">
            <h3>Offered Services</h3>
            <template x-for="category in offeredCategories" :key="category.id">
                <sl-tooltip :content="category.name">
                    <sl-icon-button :name="category.icon" :label="category.name"></sl-icon-button>
                </sl-tooltip>
            </template>
        </div>

        <sl-tab-group>
            <sl-tab slot="nav" panel="posts">Posts</sl-tab>
            <sl-tab slot="nav" panel="offers">Offers</sl-tab>
            <sl-tab slot="nav" panel="portfolio">Portfolio</sl-tab>
            <sl-tab slot="nav" panel="reviews">Reviews</sl-tab>
            <sl-tab slot="nav" panel="stats">Stats</sl-tab>
            <sl-tab slot="nav" panel="contact">Contact</sl-tab>

            <sl-tab-panel name="posts">
                <div class="posts-container">
                    <template x-for="post in posts" :key="post.id">
                        <sl-card class="post-card">
                            <h3 slot="header" x-text="post.title"></h3>
                            <p x-text="post.content"></p>
                            <div slot="footer" class="post-meta">
                                <span x-text="`Posted on: ${new Date(post.created_at).toLocaleDateString()}`"></span>
                                <span x-text="`Likes: ${post.likes_count}`"></span>
                                <span x-text="`Comments: ${post.comments_count}`"></span>
                            </div>
                            <sl-button href="`/post/${post.id}`" variant="primary">Read More</sl-button>
                        </sl-card>
                    </template>
                </div>
            </sl-tab-panel>

            <sl-tab-panel name="offers">
                <div class="offers-filter">
                    <sl-input placeholder="Filter by tags" id="tagFilter" x-model="tagFilter"></sl-input>
                    <sl-select id="categoryFilter" x-model="categoryFilter">
                        <sl-menu-item value="">All Categories</sl-menu-item>
                        <template x-for="category in offeredCategories" :key="category.id">
                            <sl-menu-item :value="category.id" x-text="category.name"></sl-menu-item>
                        </template>
                    </sl-select>
                </div>
                <div class="offers-grid">
                    <template x-for="offer in filteredOffers" :key="offer.id">
                        <sl-card class="offer-card">
                            <h4 slot="header" x-text="offer.name"></h4>
                            <p x-text="offer.description"></p>
                            <p class="price" x-text="`$${offer.price.toFixed(2)}`"></p>
                            <div class="offer-tags">
                                <template x-for="tag in offer.tags" :key="tag">
                                    <sl-badge variant="neutral" x-text="tag"></sl-badge>
                                </template>
                            </div>
                            <sl-button :href="`/${offer.type}/${offer.id}`" slot="footer" variant="primary" x-text="`View ${offer.type.charAt(0).toUpperCase() + offer.type.slice(1)}`"></sl-button>
                        </sl-card>
                    </template>
                </div>
            </sl-tab-panel>

            <sl-tab-panel name="portfolio">
                <div class="portfolio-grid">
                    <template x-for="item in portfolioItems" :key="item.id">
                        <sl-card class="portfolio-item">
                            <img slot="image" :src="item.image_url" :alt="item.title">
                            <h3 x-text="item.title"></h3>
                            <p x-text="item.description"></p>
                            <sl-button :href="`/portfolio-item/${item.id}`" slot="footer" variant="primary">View Details</sl-button>
                        </sl-card>
                    </template>
                </div>
            </sl-tab-panel>

            <sl-tab-panel name="reviews">
                <div class="reviews-container">
                    <template x-for="review in reviews" :key="review.id">
                        <sl-card class="review-card">
                            <div slot="header" class="review-header">
                                <sl-avatar :image="review.reviewer_avatar" label="Reviewer"></sl-avatar>
                                <div class="review-meta">
                                    <h4 x-text="review.reviewer_name"></h4>
                                    <sl-rating readonly :value="review.rating"></sl-rating>
                                    <span class="review-date" x-text="new Date(review.created_at).toLocaleDateString()"></span>
                                </div>
                            </div>
                            <p class="review-content" x-text="review.content"></p>
                        </sl-card>
                    </template>
                </div>
            </sl-tab-panel>

            <sl-tab-panel name="stats">
                <div class="stats-container">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="ratingChart"></canvas>
                    </div>
                </div>
            </sl-tab-panel>

            <sl-tab-panel name="contact">
                <sl-form class="contact-form" @submit="sendMessage">
                    <input type="hidden" name="recipient_id" :value="user.id">
                    <sl-input name="subject" label="Subject" required></sl-input>
                    <sl-textarea name="message" label="Message" required></sl-textarea>
                    <sl-button type="submit" variant="primary">Send Message</sl-button>
                </sl-form>
            </sl-tab-panel>
        </sl-tab-group>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
    function profileApp() {
        return {
            user: <?= json_encode($user) ?>,
            profile: <?= json_encode($profile) ?>,
            badges: <?= json_encode($badges) ?>,
            skills: <?= json_encode($skills) ?>,
            portfolioItems: <?= json_encode($portfolioItems) ?>,
            offeredCategories: <?= json_encode($offeredCategories) ?>,
            offers: <?= json_encode($offers) ?>,
            reviews: <?= json_encode($reviews) ?>,
            posts: <?= json_encode($posts) ?>,
            isOwnProfile: <?= json_encode($isOwnProfile) ?>,
            following: <?= json_encode($isFollowing) ?>,
            tagFilter: '',
            categoryFilter: '',
            get filteredOffers() {
                return this.offers.filter(offer => 
                    (this.categoryFilter === '' || offer.category_id === this.categoryFilter) &&
                    (this.tagFilter === '' || offer.tags.some(tag => tag.toLowerCase().includes(this.tagFilter.toLowerCase())))
                );
            },
            followUser(userId) {
                fetch('/follow', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.following = data.following;
                    } else {
                        alert('Failed to update follow status');
                    }
                });
            },
            sendMessage(event) {
                event.preventDefault();
                const formData = new FormData(event.target);
                fetch('/messages/send', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Message sent successfully');
                        event.target.reset();
                    } else {
                        alert('Failed to send message: ' + data.message);
                    }
                });
            },
            init() {
                this.$nextTick(() => {
                    this.initCharts();
                });
            },
            initCharts() {
                // Initialize sales chart
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($salesChartLabels) ?>,
                        datasets: [{
                            label: 'Sales',
                            data: <?= json_encode($salesChartData) ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    }
                });

                // Initialize rating chart
                new Chart(document.getElementById('ratingChart'), {
                    type: 'bar',
                    data: {
                        labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                        datasets: [{
                            label: 'Rating Distribution',
                            data: <?= json_encode($ratingDistribution) ?>,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)'
                        }]
                    }
                });
            }
        }
    }
    </script>
</body>
</html>