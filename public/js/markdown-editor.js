class MarkdownEditor extends HTMLElement {
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
                textarea {
                    width: 100%;
                    min-height: 200px;
                    padding: 10px;
                    font-family: monospace;
                }
                .preview {
                    border: 1px solid #ccc;
                    padding: 10px;
                    margin-top: 10px;
                }
            </style>
            <textarea></textarea>
            <div class="preview"></div>
        `;

        this.textarea = this.shadowRoot.querySelector('textarea');
        this.preview = this.shadowRoot.querySelector('.preview');

        this.textarea.addEventListener('input', () => this.updatePreview());
        this.updatePreview();
    }

    updatePreview() {
        const markdown = this.textarea.value;
        this.preview.innerHTML = marked.parse(markdown);
        this.dispatchEvent(new CustomEvent('input', { detail: markdown }));
    }

    get value() {
        return this.textarea.value;
    }

    set value(newValue) {
        this.textarea.value = newValue;
        this.updatePreview();
    }
}

customElements.define('markdown-editor', MarkdownEditor);