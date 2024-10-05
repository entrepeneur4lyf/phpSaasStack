<div x-data="messageCategories()">
    <h2>Manage Message Categories</h2>

    <sl-form @submit="addCategory">
        <sl-input name="name" placeholder="New Category Name" required></sl-input>
        <sl-button type="submit" variant="primary">Add Category</sl-button>
    </sl-form>

    <sl-table>
        <sl-table-head>
            <sl-table-row>
                <sl-table-cell>ID</sl-table-cell>
                <sl-table-cell>Name</sl-table-cell>
                <sl-table-cell>Actions</sl-table-cell>
            </sl-table-row>
        </sl-table-head>
        <sl-table-body>
            <template x-for="category in categories" :key="category.id">
                <sl-table-row>
                    <sl-table-cell x-text="category.id"></sl-table-cell>
                    <sl-table-cell x-text="category.name"></sl-table-cell>
                    <sl-table-cell>
                        <sl-button @click="editCategory(category.id)">Edit</sl-button>
                        <sl-button @click="deleteCategory(category.id)" variant="danger">Delete</sl-button>
                    </sl-table-cell>
                </sl-table-row>
            </template>
        </sl-table-body>
    </sl-table>

    <sl-dialog label="Edit Category" :open="editDialogOpen">
        <sl-form @submit="updateCategory">
            <sl-input name="name" :value="editingCategory.name" required></sl-input>
            <sl-button type="submit" variant="primary">Update</sl-button>
            <sl-button @click="editDialogOpen = false" variant="default">Cancel</sl-button>
        </sl-form>
    </sl-dialog>
</div>

<script>
function messageCategories() {
    return {
        categories: <?= json_encode($categories) ?>,
        editDialogOpen: false,
        editingCategory: {},

        async addCategory(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            const response = await fetch('/admin/add-message-category', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const newCategory = await response.json();
                this.categories.push(newCategory);
                form.reset();
            } else {
                alert('Failed to add category');
            }
        },

        editCategory(categoryId) {
            this.editingCategory = this.categories.find(c => c.id === categoryId);
            this.editDialogOpen = true;
        },

        async updateCategory(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('id', this.editingCategory.id);

            const response = await fetch('/admin/update-message-category', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const updatedCategory = await response.json();
                const index = this.categories.findIndex(c => c.id === updatedCategory.id);
                this.categories[index] = updatedCategory;
                this.editDialogOpen = false;
            } else {
                alert('Failed to update category');
            }
        },

        async deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                const response = await fetch(`/admin/delete-message-category/${categoryId}`, {
                    method: 'POST'
                });

                if (response.ok) {
                    this.categories = this.categories.filter(c => c.id !== categoryId);
                } else {
                    alert('Failed to delete category');
                }
            }
        }
    }
}
</script>