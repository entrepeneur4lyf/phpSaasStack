class AssetLibrary extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }

    connectedCallback() {
        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                }
                .asset-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                    gap: 10px;
                }
                .asset-item {
                    cursor: pointer;
                    text-align: center;
                }
                .asset-item img {
                    max-width: 100%;
                    height: auto;
                }
            </style>
            <sl-button @click="this.openLibrary()">Insert Asset</sl-button>
            <sl-dialog label="Asset Library">
                <div class="asset-grid"></div>
            </sl-dialog>
        `;

        this.dialog = this.shadowRoot.querySelector('sl-dialog');
        this.assetGrid = this.shadowRoot.querySelector('.asset-grid');
        this.loadAssets();
    }

    async loadAssets() {
        try {
            const response = await fetch('/api/assets');
            const assets = await response.json();
            this.renderAssets(assets);
        } catch (error) {
            console.error('Failed to load assets:', error);
        }
    }

    renderAssets(assets) {
        this.assetGrid.innerHTML = assets.map(asset => `
            <div class="asset-item" @click="this.selectAsset('${asset.id}')">
                <img src="${asset.thumbnail_url}" alt="${asset.name}">
                <div>${asset.name}</div>
            </div>
        `).join('');
    }

    openLibrary() {
        this.dialog.show();
    }

    selectAsset(assetId) {
        this.dispatchEvent(new CustomEvent('asset-selected', { detail: assetId }));
        this.dialog.hide();
    }
}

customElements.define('asset-library', AssetLibrary);