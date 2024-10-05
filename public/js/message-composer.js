class MessageComposer extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `
            <div x-data="messageComposer()">
                <sl-form @submit="sendMessage">
                    <sl-input label="To" name="recipient" x-model="form.recipient" required></sl-input>
                    <sl-input label="Subject" name="subject" x-model="form.subject" required></sl-input>
                    <sl-textarea label="Message" name="content" x-model="form.content" required></sl-textarea>
                    <sl-button type="submit" variant="primary">Send Message</sl-button>
                </sl-form>
            </div>
        `;

        Alpine.data('messageComposer', () => ({
            form: {
                recipient: '',
                subject: '',
                content: ''
            },
            async sendMessage(e) {
                e.preventDefault();
                const response = await fetch('/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });

                if (response.ok) {
                    this.form = { recipient: '', subject: '', content: '' };
                    alert('Message sent successfully');
                } else {
                    alert('Failed to send message');
                }
            }
        }));
    }
}

customElements.define('message-composer', MessageComposer);