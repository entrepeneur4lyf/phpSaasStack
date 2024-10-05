<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Portfolio</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/light.css">
</head>
<body>
    <h1>Manage Portfolio</h1>
    
    <div x-data="portfolioManager()">
        <sl-button @click="showAddForm = true">Add New Portfolio Item</sl-button>

        <sl-dialog label="Add Portfolio Item" :open="showAddForm" @sl-hide="showAddForm = false">
            <form @submit.prevent="addItem">
                <sl-input name="title" label="Title" x-model="newItem.title" required></sl-input>
                <sl-textarea name="description" label="Description" x-model="newItem.description" required></sl-textarea>
                <sl-input type="file" name="image" label="Image" @change="handleFileChange($event, 'newItem')"></sl-input>
                <sl-button type="submit" variant="primary">Add Item</sl-button>
            </form>
        </sl-dialog>

        <div class="portfolio-items">
            <template x-for="item in items" :key="item.id">
                <sl-card class="portfolio-item">
                    <img slot="image" :src="item.image_url" :alt="item.title">
                    <strong x-text="item.title"></strong>
                    <p x-text="item.description"></p>
                    <sl-button-group slot="footer">
                        <sl-button @click="editItem(item)">Edit</sl-button>
                        <sl-button @click="deleteItem(item.id)" variant="danger">Delete</sl-button>
                    </sl-button-group>
                </sl-card>
            </template>
        </div>

        <sl-dialog label="Edit Portfolio Item" :open="showEditForm" @sl-hide="showEditForm = false">
            <form @submit.prevent="updateItem">
                <sl-input name="title" label="Title" x-model="editingItem.title" required></sl-input>
                <sl-textarea name="description" label="Description" x-model="editingItem.description" required></sl-textarea>
                <sl-input type="file" name="image" label="Image" @change="handleFileChange($event, 'editingItem')"></sl-input>
                <sl-button type="submit" variant="primary">Update Item</sl-button>
            </form>
        </sl-dialog>
    </div>

    <script>
    function portfolioManager() {
        return {
            items: <?= json_encode($portfolioItems) ?>,
            newItem: { title: '', description: '', image: null },
            editingItem: null,
            showAddForm: false,
            showEditForm: false,

            addItem() {
                const formData = new FormData();
                formData.append('title', this.newItem.title);
                formData.append('description', this.newItem.description);
                if (this.newItem.image) {
                    formData.append('image', this.newItem.image);
                }

                fetch('/portfolio/add', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.items.push(data.item);
                        this.showAddForm = false;
                        this.newItem = { title: '', description: '', image: null };
                    } else {
                        alert('Failed to add portfolio item');
                    }
                });
            },

            editItem(item) {
                this.editingItem = { ...item };
                this.showEditForm = true;
            },

            updateItem() {
                const formData = new FormData();
                formData.append('item_id', this.editingItem.id);
                formData.append('title', this.editingItem.title);
                formData.append('description', this.editingItem.description);
                if (this.editingItem.newImage) {
                    formData.append('image', this.editingItem.newImage);
                }

                fetch('/portfolio/update', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const index = this.items.findIndex(item => item.id === this.editingItem.id);
                        this.items[index] = data.item;
                        this.showEditForm = false;
                        this.editingItem = null;
                    } else {
                        alert('Failed to update portfolio item');
                    }
                });
            },

            deleteItem(itemId) {
                if (confirm('Are you sure you want to delete this portfolio item?')) {
                    fetch('/portfolio/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ item_id: itemId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.items = this.items.filter(item => item.id !== itemId);
                        } else {
                            alert('Failed to delete portfolio item');
                        }
                    });
                }
            },

            handleFileChange(event, target) {
                const file = event.target.files[0];
                if (file) {
                    this[target].image = file;
                    if (target === 'editingItem') {
                        this[target].newImage = file;
                    }
                }
            }
        }
    }
    </script>
</body>
</html>