<!-- Add this inside the navigation menu, where appropriate -->
<?php if (Auth::check()): ?>
    <li><a href="/profile.php?id=<?php echo Auth::user()->id; ?>">My Profile</a></li>
<?php endif; ?>