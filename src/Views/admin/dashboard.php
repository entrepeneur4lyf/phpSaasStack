{% extends "layouts/admin.twig" %}

{% block title %}Admin Dashboard{% endblock %}

{% block content %}
<div x-data="adminDashboard()" class="admin-dashboard">
    <h1>Admin Dashboard</h1>

    <sl-tab-group>
        <sl-tab slot="nav" panel="overview">Overview</sl-tab>
        <sl-tab slot="nav" panel="products">Products</sl-tab>
        <sl-tab slot="nav" panel="orders">Orders</sl-tab>
        <sl-tab slot="nav" panel="users">Users</sl-tab>

        <sl-tab-panel name="overview">
            <h2>Site Overview</h2>
            <sl-card>
                <div class="dashboard-stats">
                    <div>Total Products: <span x-text="stats.totalProducts"></span></div>
                    <div>Total Orders: <span x-text="stats.totalOrders"></span></div>
                    <div>Total Users: <span x-text="stats.totalUsers"></span></div>
                    <div>Revenue: $<span x-text="stats.revenue.toFixed(2)"></span></div>
                </div>
            </sl-card>
        </sl-tab-panel>

        <sl-tab-panel name="products">
            <h2>Product Management</h2>
            <sl-button @click="showAddProductModal = true">Add New Product</sl-button>
            <div hx-get="{{ path('admin_products_list') }}" hx-trigger="load" hx-target="#products-list">
                <sl-spinner></sl-spinner>
            </div>
            <div id="products-list"></div>
        </sl-tab-panel>

        <sl-tab-panel name="orders">
            <h2>Order Management</h2>
            <div hx-get="{{ path('admin_orders_list') }}" hx-trigger="load" hx-target="#orders-list">
                <sl-spinner></sl-spinner>
            </div>
            <div id="orders-list"></div>
        </sl-tab-panel>

        <sl-tab-panel name="users">
            <h2>User Management</h2>
            <div hx-get="{{ path('admin_users_list') }}" hx-trigger="load" hx-target="#users-list">
                <sl-spinner></sl-spinner>
            </div>
            <div id="users-list"></div>
        </sl-tab-panel>
    </sl-tab-group>

    <sl-dialog label="Add New Product" :open="showAddProductModal">
        <sl-form @submit.prevent="addProduct">
            <sl-input name="name" label="Product Name" required></sl-input>
            <sl-textarea name="description" label="Description" required></sl-textarea>
            <sl-input type="number" name="price" label="Price" required></sl-input>
            <sl-input type="file" name="image" label="Product Image" accept="image/*"></sl-input>
            <sl-button type="submit" variant="primary">Add Product</sl-button>
        </sl-form>
    </sl-dialog>
</div>

<script>
function adminDashboard() {
    return {
        stats: {{ stats|json_encode|raw }},
        showAddProductModal: false,

        addProduct(event) {
            const form = event.target;
            const formData = new FormData(form);

            fetch('{{ path('admin_add_product') }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showAddProductModal = false;
                    // Refresh the products list
                    htmx.trigger('#products-list', 'refreshProducts');
                } else {
                    alert('Failed to add product: ' + data.message);
                }
            });
        },

        // Add more methods as needed for other dashboard functionalities
    }
}
</script>

{% block websocket %}
<script>
    const socket = new WebSocket('{{ websocket_url }}');
    
    socket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        if (data.type === 'stats_update') {
            // Update dashboard stats in real-time
            Alpine.store('stats', data.stats);
        }
    };
</script>
{% endblock %}
{% endblock %}