<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/light.css">
</head>
<body>
    <div x-data="composeMessage()">
        <h1>Compose Message</h1>
        
        <form @submit.prevent="sendMessage">
            <sl-select name="recipient_id" label="Recipient" x-model="recipient" required>
                <template x-for="user in users" :key="user.id">
                    <sl-menu-item :value="user.id" x-text="user.username"></sl-menu-item>
                </template>
            </sl-select>

            <sl-input name="subject" label="Subject" x-model="subject" required></sl-input>

            <sl-textarea name="content" label="Message" x-model="content" required></sl-textarea>

            <sl-button type="submit" variant="primary">Send Message</sl-button>
        </form>
    </div>

    <script>
    function composeMessage() {
        return {
            users: <?= json_encode($users) ?>,
            recipient: '',
            subject: '',
            content: '',
            sendMessage() {
                fetch('/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        recipient_id: this.recipient,
                        subject: this.subject,
                        content: this.content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/messages';
                    } else {
                        alert('Failed to send message: ' + data.message);
                    }
                });
            }
        }
    }
    </script>
</body>
</html>