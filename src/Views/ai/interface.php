<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Inference Service</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.85/dist/themes/dark.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.85/dist/shoelace.js"></script>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="container" x-data="aiInterface()">
        <h1>AI Inference Service</h1>
        <sl-form @submit.prevent="submitPrompt">
            <sl-textarea name="prompt" label="Enter your prompt" x-model="prompt"></sl-textarea>
            <sl-button type="submit" variant="primary" :loading="loading">Submit</sl-button>
        </sl-form>
        <sl-details summary="AI Response" x-show="response">
            <pre x-text="response"></pre>
        </sl-details>
    </div>

    <script>
    function aiInterface() {
        return {
            prompt: '',
            response: '',
            loading: false,

            async submitPrompt() {
                this.loading = true;
                try {
                    const response = await fetch('/ai/chat-completion', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({ prompt: this.prompt })
                    });
                    const data = await response.json();
                    this.response = data.response;
                } catch (error) {
                    console.error('Error:', error);
                    this.response = 'An error occurred while processing your request.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
</body>
</html>