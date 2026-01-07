<?php
    require_once 'includes/config.php';
    require_once 'includes/functions.php';

    // Sprawdza czy użytkownik jest zalogowany
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    // Pobiera dane zalogowanego użytkownika
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();

    // Pobiera sugestie użytkowników do obserwowania (max 5, losowo)
    $stmt = $pdo->prepare("
        SELECT id, username, full_name, bio, profile_image, verified 
        FROM users 
        WHERE id != ? 
        AND id NOT IN (
            SELECT following_id FROM follows WHERE follower_id = ?
        )
        ORDER BY RAND()
        LIMIT 5
    ");
    $stmt->execute([$userId, $userId]);
    $suggestedUsers = $stmt->fetchAll();

    // Ustawienia dla nagłówka
    $pageTitle = 'Strona główna / Platforma Y';
    $cssFile = 'css/style.css';
    
    require_once 'includes/header.php';
?>


<?php require_once 'includes/left_sidebar.php'; ?>

<!-- Główna zawartość -->
<main class="main-content">
    <!-- Nagłówek -->
    <header class="feed-header">
        <div class="feed-tabs">
            <button class="feed-tab active" data-tab="for-you">Dla Ciebie</button>
            <button class="feed-tab" data-tab="following">Obserwowani</button>
        </div>
    </header>

    <!-- Formularz nowego posta -->
    <div class="new-post">
        <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Avatar" class="post-avatar">
        <div class="post-form">
            <textarea placeholder="Co się dzieje?!" maxlength="280"></textarea>
            
            <!-- Podgląd wybranego zdjęcia -->
            <div class="image-preview" style="display: none;">
                <img src="" alt="Podgląd" class="preview-image">
                <button class="remove-image-btn" title="Usuń zdjęcie">
                    <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: white;">
                        <g><path d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"></path></g>
                    </svg>
                </button>
            </div>
            
            <input type="file" id="image-upload" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
            
            <div class="post-actions">
                <div class="post-icons">
                    <button class="icon-btn" id="media-btn" title="Zdjęcie lub GIF">
                        <svg viewBox="0 0 24 24"><g><path d="M3 5.5C3 4.119 4.119 3 5.5 3h13C19.881 3 21 4.119 21 5.5v13c0 1.381-1.119 2.5-2.5 2.5h-13C4.119 21 3 19.881 3 18.5v-13zM5.5 5c-.276 0-.5.224-.5.5v13c0 .276.224.5.5.5h13c.276 0 .5-.224.5-.5v-13c0-.276-.224-.5-.5-.5h-13zM18 15.5c0-.276-.224-.5-.5-.5H17v.5c0 .276-.224.5-.5.5s-.5-.224-.5-.5V15h-.5c-.276 0-.5-.224-.5-.5s.224-.5.5-.5h.5v-.5c0-.276.224-.5.5-.5s.5.224.5.5v.5h.5c.276 0 .5.224.5.5zm-3-5.5c0-1.105-.895-2-2-2s-2 .895-2 2 .895 2 2 2 2-.895 2-2z"></path></g></svg>
                    </button>
                    <button class="icon-btn" id="emoji-btn" title="Emoji">
                        <svg viewBox="0 0 24 24"><g><path d="M8 9.5C8 8.119 8.672 7 9.5 7S11 8.119 11 9.5 10.328 12 9.5 12 8 10.881 8 9.5zm6.5 2.5c.828 0 1.5-1.119 1.5-2.5S15.328 7 14.5 7 13 8.119 13 9.5s.672 2.5 1.5 2.5zM12 16c-2.224 0-3.021-2.227-3.051-2.316l-1.897.633c.05.15 1.271 3.684 4.949 3.684s4.898-3.533 4.949-3.684l-1.896-.638c-.033.095-.83 2.322-3.053 2.322zm10.25-4.001c0 5.652-4.598 10.25-10.25 10.25S1.75 17.652 1.75 12 6.348 1.75 12 1.75 22.25 6.348 22.25 12zm-2 0c0-4.549-3.701-8.25-8.25-8.25S3.75 7.451 3.75 12s3.701 8.25 8.25 8.25 8.25-3.701 8.25-8.25z"></path></g></svg>
                    </button>
                </div>
                <button class="post-submit-btn">Postuj</button>
            </div>
        </div>
    </div>
    
    <!-- Emoji Picker -->
    <div class="emoji-picker" id="emoji-picker" style="display: none;">
        <div class="emoji-picker-header">
            <span>Wybierz emoji</span>
            <button class="emoji-close-btn">×</button>
        </div>
        <div class="emoji-categories">
            <button class="emoji-category active" data-category="smileys">😀</button>
            <button class="emoji-category" data-category="gestures">👋</button>
            <button class="emoji-category" data-category="animals">🐶</button>
            <button class="emoji-category" data-category="food">🍕</button>
            <button class="emoji-category" data-category="activities">⚽</button>
            <button class="emoji-category" data-category="travel">✈️</button>
            <button class="emoji-category" data-category="objects">💡</button>
            <button class="emoji-category" data-category="symbols">❤️</button>
        </div>
        <div class="emoji-list" id="emoji-list"></div>
    </div>

    <!-- Separator -->
    <div class="posts-separator"></div>

    <!-- Feed z postami (ładowany dynamicznie) -->
    <div class="feed">
        <!-- Posty będą ładowane przez JavaScript -->
    </div>
</main>

<?php require_once 'includes/right_sidebar.php'; ?>

<div id="current-user-data" 
     data-user-id="<?php echo $currentUser['id']; ?>"
     data-username="<?php echo htmlspecialchars($currentUser['username']); ?>"
     style="display: none;">
</div>

<?php require_once 'includes/footer.php'; ?>
