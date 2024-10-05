<div x-data="offers()" class="offers-container">
    <h1>Offers</h1>

    <sl-button @click="showCreateOfferModal = true">Create New Offer</sl-button>

    <div class="offers-list">
        <template x-for="offer in offers" :key="offer.id">
            <sl-card class="offer-card">
                <h2 slot="header" x-text="offer.name"></h2>
                <div x-html="renderMarkdown(offer.description)"></div>
                <p x-text="'Price: $' + offer.price"></p>
                <div slot="footer">
                    <sl-button @click="editOffer(offer)">Edit</sl-button>
                    <sl-button @click="deleteOffer(offer.id)" variant="danger">Delete</sl-button>
                </div>
            </sl-card>
        </template>
    </div>

    <sl-dialog label="Create New Offer" :open="showCreateOfferModal">
        <sl-form @submit="createOffer">
            <sl-input name="name" label="Offer Name" required></sl-input>
            <div id="create-offer-editor"></div>
            <asset-library @asset-selected="insertAsset"></asset-library>
            <sl-input name="price" label="Price" type="number" required></sl-input>
            <sl-button type="submit" variant="primary">Create Offer</sl-button>
            <sl-button @click="showCreateOfferModal = false" variant="default">Cancel</sl-button>
        </sl-form>
    </sl-dialog>

    <sl-dialog label="Edit Offer" :open="showEditOfferModal">
        <sl-form @submit="updateOffer">
            <sl-input name="name" label="Offer Name" x-model="editingOffer.name" required></sl-input>
            <markdown-editor x-model="editingOffer.description" placeholder="Edit your offer description here..."></markdown-editor>
            <asset-library @asset-selected="insertAsset"></asset-library>
            <sl-input name="price" label="Price" type="number" x-model="editingOffer.price" required></sl-input>
            <sl-button type="submit" variant="primary">Update Offer</sl-button>
            <sl-button @click="showEditOfferModal = false" variant="default">Cancel</sl-button>
        </sl-form>
    </sl-dialog>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
function offers() {
    let createOfferEditor;

    return {
        offers: <?= json_encode($offers) ?>,
        showCreateOfferModal: false,
        showEditOfferModal: false,
        editingOffer: null,

        init() {
            this.$watch('showCreateOfferModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        createOfferEditor = new TinyMDE.Editor({
                            element: 'create-offer-editor',
                            content: ''
                        });
                    });
                }
            });

            this.$watch('showEditOfferModal', value => {
                if (value) {
                    this.$nextTick(() => {
                        editOfferEditor = new TinyMDE.Editor({
                            element: 'edit-offer-editor',
                            content: this.editingOffer.description
                        });
                    });
                }
            });
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
            if (this.showEditOfferModal) {
                editOfferEditor.insertText(assetMarkdown);
            } else {
                createOfferEditor.insertText(assetMarkdown);
            }
        },

        async createOffer(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('description', createOfferEditor.getContent());

            const response = await fetch('/offers/create', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const newOffer = await response.json();
                this.offers.unshift(newOffer);
                this.showCreateOfferModal = false;
                this.newOfferDescription = '';
                form.reset();
            } else {
                alert('Failed to create offer');
            }
        },

        editOffer(offer) {
            this.editingOffer = { ...offer };
            this.showEditOfferModal = true;
        },

        async updateOffer(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('id', this.editingOffer.id);
            formData.append('description', editOfferEditor.getContent());

            const response = await fetch(`/offers/update/${this.editingOffer.id}`, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const updatedOffer = await response.json();
                const index = this.offers.findIndex(o => o.id === updatedOffer.id);
                this.offers[index] = updatedOffer;
                this.showEditOfferModal = false;
                this.editingOffer = null;
            } else {
                alert('Failed to update offer');
            }
        },

        async deleteOffer(offerId) {
            if (confirm('Are you sure you want to delete this offer?')) {
                const response = await fetch(`/offers/delete/${offerId}`, {
                    method: 'POST'
                });

                if (response.ok) {
                    this.offers = this.offers.filter(o => o.id !== offerId);
                } else {
                    alert('Failed to delete offer');
                }
            }
        }
    }
}
</script>