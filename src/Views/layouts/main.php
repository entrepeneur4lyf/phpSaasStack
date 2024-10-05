<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'AI Inference Service' ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.85/dist/themes/light.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tiny-markdown-editor@0.1.3/dist/tiny-mde.min.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.85/dist/shoelace.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/tiny-markdown-editor@0.1.3/dist/tiny-mde.min.js"></script>
    <script src="/js/asset-library.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>