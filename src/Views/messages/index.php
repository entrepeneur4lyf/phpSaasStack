<div x-data="messageSystem()" class="messages-container">
    <h2>Messages</h2>
    
    <sl-tab-group>
        <sl-tab slot="nav" panel="inbox">Inbox</sl-tab>
        <sl-tab slot="nav" panel="sent">Sent</sl-tab>
        <sl-tab slot="nav" panel="compose">Compose</sl-tab>

        <sl-tab-panel name="inbox">
            <div class="message-list">
                <template x-for="message in inboxMessages" :key="message.id">
                    <sl-card class="message-item" :class="{ 'unread': !message.is_read }">
                        <div slot="header">
                            <span x-text="message.category_name" class="message-category"></span>
                            <h3 x-text="message.subject"></h3>
                        </div>
                        <p>From: <span x-text="message.sender_name"></span></p>
                        <p>Date: <span x-text="formatDate(message.created_at)"></span></p>
                        <sl-button @click="viewMessage(message.id)">View Message</sl-button>
                    </sl-card>
                </template>
            </div>
        </sl-tab-panel>

        <sl-tab-panel name="sent">
            <div class="message-list">
                <template x-for="message in sentMessages" :key="message.id">
                    <sl-card class="message-item">
                        <div slot="header">
                            <span x-text="message.category_name" class="message-category"></span>
                            <h3 x-text="message.subject"></h3>
                        </div>
                        <p>To: <span x-text="message.recipient_name"></span></p>
                        <p>Date: <span x-text="formatDate(message.created_at)"></span></p>
                        <sl-button @click="viewMessage(message.id)">View Message</sl-button>
                    </sl-card>
                </template>
            </div>
        </sl-tab-panel>

        <sl-tab-panel name="compose">
            <sl-form @submit="sendMessage">
                <sl-select name="recipient_id" label="To" required>
                    <template x-for="user in users" :key="user.id">
                        <sl-menu-item :value="user.id" x-text="user.username"></sl-menu-item>
                    </template>
                </sl-select>
                <sl-select name="category_id" label="Category" required>
                    <template x-for="category in categories" :key="category.id">
                        <sl-menu-item :value="category.id" x-text="category.name"></sl-menu-item>
                    </template>
                </sl-select>
                <sl-input name="subject" label="Subject" required></sl-input>
                <sl-textarea name="content" label="Message" required></sl-textarea>
                <sl-button type="submit" variant="primary">Send Message</sl-button>
            </sl-form>
        </sl-tab-panel>
    </sl-tab-group>
</div>

<script>
function messageSystem() {
    return {
        inboxMessages: <?= json_encode($inboxMessages) ?>,
        sentMessages: <?= json_encode($sentMessages) ?>,
        users: <?= json_encode($users) ?>,
        categories: <?= json_encode($categories) ?>,

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        },

        viewMessage(messageId) {
            window.location.href = `/messages/view/${messageId}`;
        },

        async sendMessage(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            const response = await fetch('/messages/send', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                form.reset();
                alert('Message sent successfully');
            } else {
                alert('Failed to send message');
            }
        }
    }
}
</script>