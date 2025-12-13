<?php
// includes/left_sidebar.php

// This file requires $currentUser to be set in the including file
if (!isset($currentUser)) {
    // Fallback if currentUser is not set, though it should be.
    // This could redirect or show a generic state, but for now, we'll assume it's always passed.
    // To be safe, let's prevent errors.
    $currentUser = [
        'profile_image' => 'images/default-avatar.png',
        'full_name' => 'Guest',
        'username' => 'guest',
        'verified' => false
    ];
}

// Determine the active page to highlight the nav item
$activePage = basename($_SERVER['PHP_SELF']);

?>
<aside class="sidebar-left">
    <div class="sidebar-content">
        <!-- Logo -->
        <a href="index.php" class="logo-link">
            <svg viewBox="0 0 24 24" class="logo-icon" aria-hidden="true">
                <g>
                    <path d="m 18.244,2.25 h 3.308 l -7.227,8.26 8.502,11.24 H 16.17 c 0,0 -4.248284,-5.298111 -14.916,-19.5 H 8.08 l 4.713,6.231 z m -1.161,17.52 h 1.833 L 7.084,4.126 H 5.117 Z" fill="#e7e9ea"></path>
                </g>
            </svg>
        </a>

        <!-- Menu nawigacyjne -->
        <nav class="main-nav">
            <a href="index.php" class="nav-item <?php echo ($activePage == 'index.php') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <g><path d="M12 3L3 9v11c0 1.1.9 2 2 2h5v-7h4v7h5c1.1 0 2-.9 2-2V9l-9-6z"></path></g>
                </svg>
                <span class="nav-text">Strona główna</span>
            </a>

            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <g><path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path></g>
                </svg>
                <span class="nav-text">Szukaj</span>
            </a>

            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <g><path d="M19.993 9.042C19.48 5.017 16.054 2 11.996 2s-7.49 3.021-7.999 7.051L2.866 18H7.1c.463 2.282 2.481 4 4.9 4s4.437-1.718 4.9-4h4.236l-1.143-8.958zM12 20c-1.306 0-2.417-.835-2.829-2h5.658c-.412 1.165-1.523 2-2.829 2zm-6.866-4l.847-6.698C6.364 6.272 8.941 4 11.996 4s5.627 2.268 6.013 5.295L18.864 16H5.134z"></path></g>
                </svg>
                <span class="nav-text">Powiadomienia</span>
            </a>

            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <g><path d="M1.998 5.5c0-1.381 1.119-2.5 2.5-2.5h15c1.381 0 2.5 1.119 2.5 2.5v13c0 1.381-1.119 2.5-2.5 2.5h-15c-1.381 0-2.5-1.119-2.5-2.5v-13zm2.5-.5c-.276 0-.5.224-.5.5v2.764l8 3.638 8-3.636V5.5c0-.276-.224-.5-.5-.5h-15zm15.5 5.463l-8 3.636-8-3.638V18.5c0 .276.224.5.5.5h15c.276 0 .5-.224.5-.5v-8.037z"></path></g>
                </svg>
                <span class="nav-text">Wiadomości</span>
            </a>

            <a href="profile.php" class="nav-item <?php echo ($activePage == 'profile.php') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <g><path d="M5.651 19h12.698c-.337-1.8-1.023-3.21-1.945-4.19C15.318 13.65 13.838 13 12 13s-3.317.65-4.404 1.81c-.922.98-1.608 2.39-1.945 4.19zm.486-5.56C7.627 11.85 9.648 11 12 11s4.373.85 5.863 2.44c1.477 1.58 2.366 3.8 2.632 6.46l.11 1.1H3.395l.11-1.1c.266-2.66 1.155-4.88 2.632-6.46zM12 4c-1.105 0-2 .9-2 2s.895 2 2 2 2-.9 2-2-.895-2-2-2zM8 6c0-2.21 1.791-4 4-4s4 1.79 4 4-1.791 4-4 4-4-1.79-4-4z"></path></g>
                </svg>
                <span class="nav-text">Profil</span>
            </a>

            <a href="#" class="nav-item">
                <svg viewBox="0 0 24 24" class="nav-icon">
                    <g><path d="M3.75 12c0-4.56 3.69-8.25 8.25-8.25s8.25 3.69 8.25 8.25-3.69 8.25-8.25 8.25S3.75 16.56 3.75 12zM12 1.75C6.34 1.75 1.75 6.34 1.75 12S6.34 22.25 12 22.25 22.25 17.66 22.25 12 17.66 1.75 12 1.75zm-4.75 11.5c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25S6 11.31 6 12s.56 1.25 1.25 1.25zm9.5 0c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25-1.25.56-1.25 1.25.56 1.25 1.25 1.25zM13.25 12c0 .69-.56 1.25-1.25 1.25s-1.25-.56-1.25-1.25.56-1.25 1.25-1.25 1.25.56 1.25 1.25z"></path></g>
                </svg>
                <span class="nav-text">Więcej</span>
            </a>
        </nav>

        <!-- Przycisk "Postuj" -->
        <button class="post-button">Postuj</button>

        <!-- Informacje o użytkowniku -->
        <div class="user-info">
            <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Avatar" class="user-avatar">
            <div class="user-details">
                <div class="user-name">
                    <?php echo htmlspecialchars($currentUser['full_name']); ?>
                    <?php if ($currentUser['verified']): ?>
                        <svg viewBox="0 0 24 24" class="verified-badge" style="width: 18px; height: 18px; fill: #1D9BF0; display: inline-block; vertical-align: middle;">
                            <g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="user-username">@<?php echo htmlspecialchars($currentUser['username']); ?></div>
            </div>
            <a href="includes/logout.php" style="text-decoration: none; color: inherit;" title="Wyloguj">
                <svg viewBox="0 0 24 24" class="more-icon">
                    <g><circle cx="5" cy="12" r="2"></circle><circle cx="12" cy="12" r="2"></circle><circle cx="19" cy="12" r="2"></circle></g>
                </svg>
            </a>
        </div>
    </div>
</aside>
