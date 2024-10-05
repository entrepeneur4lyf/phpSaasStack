<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Inference Service</title>
    <link rel="stylesheet" href="/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/dark.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main id="app" x-data="{ message: 'Welcome to AI Inference Service' }">
        <h1 x-text="message"></h1>
        <sl-button @click="message = 'Empowering your projects with cutting-edge AI technology'">
            Update Message
        </sl-button>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="/js/app.js"></script>
</body>
</html>