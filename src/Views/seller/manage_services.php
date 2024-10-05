<div x-data="serviceManager()" class="service-manager">
    <h1>Manage Services</h1>
    
    <sl-form @submit.prevent="addService">
        <h2>Add New Service</h2>
        <sl-select name="category_id" label="Category" required>
            <template x-for="category in categories">
                <sl-menu-item :value="category.id" x-text="category.name"></sl-menu-item>
            </template>
        </sl-select>

        <sl-input name="title" label="Title" required></sl-input>
        <sl-textarea name="description" label="Description" required></sl-textarea>
        <sl-input type="number" name="price" label="Price" required></sl-input>
        <sl-input type="number" name="delivery_time" label="Delivery Time (days)" required></sl-input>

        <sl-button type="submit">Add Service</sl-button>
    </sl-form>

    <h2>Your Services</h2>
    <div class="services-grid">
        <template x-for="service in services" :key="service.id">
            <sl-card class="service-card">
                <h3 x-text="service.title"></h3>
                <p x-text="service.description"></p>
                <p class="price" x-text="'$' + service.price.toFixed(2)"></p>
                <p x-text="'Delivery: ' + service.delivery_time + ' days'"></p>
                <sl-button @click="editService(service.id)">Edit</sl-button>
            </sl-card>
        </template>
    </div>
</div>

<script>
function serviceManager() {
    return {
        services: <?= json_encode($services) ?>,
        categories: <?= json_encode($categories) ?>,

        addService(event) {
            const form = event.target;
            const formData = new FormData(form);

            fetch('/seller/manage-services', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to add service');
                }
            });
        },

        editService(serviceId) {
            window.location.href = `/seller/edit-service/${serviceId}`;
        }
    }
}
</script>