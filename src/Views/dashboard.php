<div x-data="dashboard()" class="dashboard-container">
    <h1>Welcome, <?= htmlspecialchars($user->username) ?></h1>

    <sl-tab-group>
        <sl-tab slot="nav" panel="overview">Overview</sl-tab>
        <sl-tab slot="nav" panel="stats">Statistics</sl-tab>
        <sl-tab slot="nav" panel="activity">Recent Activity</sl-tab>

        <sl-tab-panel name="overview">
            <sl-card>
                <h2 slot="header">Quick Stats</h2>
                <div class="quick-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Posts</span>
                        <span class="stat-value" x-text="stats.totalPosts"></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Offers</span>
                        <span class="stat-value" x-text="stats.totalOffers"></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Followers</span>
                        <span class="stat-value" x-text="stats.followers"></span>
                    </div>
                </div>
            </sl-card>
        </sl-tab-panel>

        <sl-tab-panel name="stats">
            <!-- Add more detailed statistics here -->
        </sl-tab-panel>

        <sl-tab-panel name="activity">
            <sl-card>
                <h2 slot="header">Recent Activity</h2>
                <ul class="activity-list">
                    <template x-for="activity in recentActivity" :key="activity.id">
                        <li class="activity-item">
                            <span x-text="activity.description"></span>
                            <span x-text="formatDate(activity.created_at)" class="activity-date"></span>
                        </li>
                    </template>
                </ul>
            </sl-card>
        </sl-tab-panel>
    </sl-tab-group>
</div>

<script>
function dashboard() {
    return {
        stats: <?= json_encode($stats) ?>,
        recentActivity: <?= json_encode($recentActivity) ?>,

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }
    }
}
</script>