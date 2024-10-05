<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/dark.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="container">
        <h1>Edit Profile</h1>
        
        <sl-form action="/profile/update" method="POST" enctype="multipart/form-data">
            <sl-textarea name="bio" label="Bio"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></sl-textarea>
            
            <sl-input name="website" label="Website" type="url" value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>"></sl-input>

            <sl-details summary="Social Media Links">
                <sl-input name="twitter" label="Twitter" type="url" value="<?php echo htmlspecialchars($profile['social_media']['twitter'] ?? ''); ?>"></sl-input>
                <sl-input name="facebook" label="Facebook" type="url" value="<?php echo htmlspecialchars($profile['social_media']['facebook'] ?? ''); ?>"></sl-input>
                <sl-input name="instagram" label="Instagram" type="url" value="<?php echo htmlspecialchars($profile['social_media']['instagram'] ?? ''); ?>"></sl-input>
            </sl-details>

            <sl-input name="avatar" label="Avatar" type="file" accept="image/*"></sl-input>
            <?php if ($profile['avatar_url']): ?>
                <img src="<?php echo htmlspecialchars($profile['avatar_url']); ?>" alt="Current Avatar" class="current-avatar">
            <?php endif; ?>

            <sl-input name="location" label="Location" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>"></sl-input>

            <sl-input name="skills" label="Skills (comma-separated)" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>"></sl-input>

            <sl-button type="submit" variant="primary">Update Profile</sl-button>
        </sl-form>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>