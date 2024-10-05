<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/light.css">
</head>
<body>
    <div x-data="messageIndex()">
        <h1>Messages</h1>
        
        <sl-button href="/messages/compose">Compose New Message</sl-button>

        <template x-for="message in messages" :key="message.id">
            <sl-card class="message-card">
                <strong x-text="message.subject"></strong>
                <p>From: <span x-text="message.sender_name"></span></p>
                <p>Date: <span x-text="formatDate(message.created_at)"></span></p>
                <sl-button slot="footer" @click="viewMessage(message.id)">
                    View Message
                </sl-button>
            </sl-card>
        </template>
    </div>

    <script>
    function messageIndex() {
        return {
            messages: <?= json_encode($messages) ?>,
            formatDate(dateString) {
                return new Date(dateString).toLocaleString();
            },
            viewMessage(id) {
                window.location.href = `/messages/view/${id}`;
            }
        }
    }
    </script>
</body>
</html>