<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/light.css">
</head>
<body>
    <div x-data="viewMessage()">
        <h1>View Message</h1>
        
        <sl-card>
            <strong slot="header" x-text="message.subject"></strong>
            <p>From: <span x-text="message.sender_name"></span></p>
            <p>Date: <span x-text="formatDate(message.created_at)"></span></p>
            <p x-html="formatContent(message.content)"></p>
            <sl-button slot="footer" @click="backToMessages">Back to Messages</sl-button>
        </sl-card>
    </div>

    <script>
    function viewMessage() {
        return {
            message: <?= json_encode($message) ?>,
            formatDate(dateString) {
                return new Date(dateString).toLocaleString();
            },
            formatContent(content) {
                return content.replace(/\n/g, '<br>');
            },
            backToMessages() {
                window.location.href = '/messages';
            }
        }
    }
    </script>
</body>
</html>