<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Sprawdza czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Pobiera nazwę użytkownika z URL lub pokazuje profil zalogowanego użytkownika
$username = isset($_GET['user']) ? clean($_GET['user']) : null;

if (!$username) {
    // Jeśli nie podano username, pokaż profil zalogowanego użytkownika
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $currentUserData = $stmt->fetch();
    $username = $currentUserData['username'];
}

// Pobiera pełne dane użytkownika profilu
$stmt = $pdo->prepare("
    SELECT 
        u.*,
        (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as posts_count,
        (SELECT COUNT(*) FROM follows WHERE follower_id = u.id) as following_count,
        (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers_count,
        EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following
    FROM users u
    WHERE u.username = ?
");
$stmt->execute([$currentUserId, $username]);
$profileUser = $stmt->fetch();

// Jeśli użytkownik nie istnieje, wraca na stronę główną
if (!$profileUser) {
    header('Location: index.php');
    exit;
}

// Sprawdza czy to własny profil
$isOwnProfile = ($profileUser['id'] == $currentUserId);

// Pobiera dane zalogowanego użytkownika dla sidebar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUserId]);
$currentUser = $stmt->fetch();

// Formatuje datę dołączenia
$joinDate = new DateTime($profileUser['created_at']);
$joinDateFormatted = $joinDate->format('F Y');
$joinDatePolish = [
    'January' => 'styczeń',
    'February' => 'luty',
    'March' => 'marzec',
    'April' => 'kwiecień',
    'May' => 'maj',
    'June' => 'czerwiec',
    'July' => 'lipiec',
    'August' => 'sierpień',
    'September' => 'wrzesień',
    'October' => 'październik',
    'November' => 'listopad',
    'December' => 'grudzień'
];
$joinDateFormatted = str_replace(array_keys($joinDatePolish), array_values($joinDatePolish), $joinDateFormatted);

// Pobiera sugestie użytkowników do obserwowania (tylko dla profili innych użytkowników)
$suggestedUsers = [];
if (!$isOwnProfile) {
    $stmt = $pdo->prepare("
        SELECT id, username, full_name, bio, profile_image, verified 
        FROM users 
        WHERE id != ? 
        AND id != ?
        AND id NOT IN (
            SELECT following_id FROM follows WHERE follower_id = ?
        )
        ORDER BY RAND()
        LIMIT 3
    ");
    $stmt->execute([$currentUserId, $profileUser['id'], $currentUserId]);
    $suggestedUsers = $stmt->fetchAll();
}

// Ustawienia dla nagłówka
$pageTitle = htmlspecialchars($profileUser['full_name']) . ' (@' . htmlspecialchars($profileUser['username']) . ') / Platforma Y';
$cssFile = 'css/profile.css';

require_once 'includes/header.php'; 
?>

<?php require_once 'includes/left_sidebar.php'; ?>

<!-- Główna zawartość profilu -->
<main class="profile-content">
    <!-- Nagłówek profilu -->
    <header class="profile-header">
        <a href="index.php" class="back-btn">
            <svg viewBox="0 0 24 24">
                <g><path d="M7.414 13l5.043 5.04-1.414 1.42L3.586 12l7.457-7.46 1.414 1.42L7.414 11H21v2H7.414z"></path></g>
            </svg>
        </a>
        <div class="header-info">
            <h2><?php echo htmlspecialchars($profileUser['full_name']); ?></h2>
            <span class="posts-count"><?php echo formatNumber($profileUser['posts_count']); ?> <?php echo $profileUser['posts_count'] == 1 ? 'post' : 'postów'; ?></span>
        </div>
    </header>

    <!-- Banner profilu -->
    <div class="profile-banner">
        <?php if ($profileUser['banner_image']): ?>
            <img src="<?php echo htmlspecialchars($profileUser['banner_image']); ?>" alt="Banner" id="bannerImg">
        <?php else: ?>
            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
        <?php endif; ?>
    </div>

    <!-- Informacje o profilu -->
    <div class="profile-info-section">
        <div class="profile-avatar-wrapper">
            <img src="<?php echo htmlspecialchars($profileUser['profile_image']); ?>" alt="Avatar" class="profile-avatar">
        </div>
        
        <div class="profile-actions">
            <?php if ($isOwnProfile): ?>
                <button class="edit-profile-btn" id="editProfileBtn">Edytuj profil</button>
            <?php else: ?>
                <button class="follow-btn <?php echo $profileUser['is_following'] ? 'following' : ''; ?>" 
                        data-user-id="<?php echo $profileUser['id']; ?>">
                    <?php echo $profileUser['is_following'] ? 'Obserwujesz' : 'Obserwuj'; ?>
                </button>
            <?php endif; ?>
        </div>

        <div class="profile-details">
            <h1 class="profile-name">
                <?php echo htmlspecialchars($profileUser['full_name']); ?>
                <?php if ($profileUser['verified']): ?>
                    <svg viewBox="0 0 22 22" class="verified-badge">
                        <g><path d="M20.396 11c-.018-.646-.215-1.275-.57-1.816-.354-.54-.852-.972-1.438-1.246.223-.607.27-1.264.14-1.897-.131-.634-.437-1.218-.882-1.687-.47-.445-1.053-.75-1.687-.882-.633-.13-1.29-.083-1.897.14-.273-.587-.704-1.086-1.245-1.44S11.647 1.62 11 1.604c-.646.017-1.273.213-1.813.568s-.969.854-1.24 1.44c-.608-.223-1.267-.272-1.902-.14-.635.13-1.22.436-1.69.882-.445.47-.749 1.055-.878 1.688-.13.633-.08 1.29.144 1.896-.587.274-1.087.705-1.443 1.245-.356.54-.555 1.17-.574 1.817.02.647.218 1.276.574 1.817.356.54.856.972 1.443 1.245-.224.606-.274 1.263-.144 1.896.13.634.433 1.218.877 1.688.47.443 1.054.747 1.687.878.633.132 1.29.084 1.897-.136.274.586.705 1.084 1.246 1.439.54.354 1.17.551 1.816.569.647-.016 1.276-.213 1.817-.567s.972-.854 1.245-1.44c.604.239 1.266.296 1.903.164.636-.132 1.22-.447 1.68-.907.46-.46.776-1.044.908-1.681s.075-1.299-.165-1.903c.586-.274 1.084-.705 1.439-1.246.354-.54.551-1.17.569-1.816zM9.662 14.85l-3.429-3.428 1.293-1.302 2.072 2.072 4.4-4.794 1.347 1.246z"></path></g>
                    </svg>
                <?php endif; ?>
            </h1>
            <p class="profile-username">@<?php echo htmlspecialchars($profileUser['username']); ?></p>
            
            <?php if ($profileUser['bio']): ?>
                <p class="profile-bio">
                    <?php echo nl2br(htmlspecialchars($profileUser['bio'])); ?>
                </p>
            <?php endif; ?>

            <div class="profile-meta">
                <?php if ($profileUser['location']): ?>
                    <span class="meta-item">
                        <svg viewBox="0 0 24 24" class="meta-icon">
                            <g><path d="M12 7c-1.93 0-3.5 1.57-3.5 3.5S10.07 14 12 14s3.5-1.57 3.5-3.5S13.93 7 12 7zm0 5c-.827 0-1.5-.673-1.5-1.5S11.173 9 12 9s1.5.673 1.5 1.5S12.827 12 12 12zm0-10c-4.687 0-8.5 3.813-8.5 8.5 0 5.967 7.621 11.116 7.945 11.332l.555.37.555-.37c.324-.216 7.945-5.365 7.945-11.332C20.5 5.813 16.687 2 12 2zm0 17.77c-1.665-1.241-6.5-5.196-6.5-9.27C5.5 6.916 8.416 4 12 4s6.5 2.916 6.5 6.5c0 4.073-4.835 8.028-6.5 9.27z"></path></g>
                        </svg>
                        <?php echo htmlspecialchars($profileUser['location']); ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($profileUser['website']): ?>
                    <span class="meta-item">
                        <svg viewBox="0 0 24 24" class="meta-icon">
                            <g><path d="M18.36 5.64c-1.95-1.96-5.11-1.96-7.07 0L9.88 7.05 8.46 5.64l1.42-1.42c2.73-2.73 7.16-2.73 9.9 0 2.73 2.74 2.73 7.17 0 9.9l-1.42 1.42-1.41-1.42 1.41-1.41c1.96-1.96 1.96-5.12 0-7.07zm-2.12 3.53l-7.07 7.07-1.41-1.41 7.07-7.07 1.41 1.41zm-12.02.71l1.42-1.42 1.41 1.42-1.41 1.41c-1.96 1.96-1.96 5.12 0 7.07 1.95 1.96 5.11 1.96 7.07 0l1.41-1.41 1.42 1.41-1.42 1.42c-2.73 2.73-7.16 2.73-9.9 0-2.73-2.74-2.73-7.17 0-9.9z"></path></g>
                        </svg>
                        <a href="<?php echo htmlspecialchars($profileUser['website']); ?>" target="_blank" rel="noopener noreferrer" class="profile-website">
                            <?php echo htmlspecialchars(parse_url($profileUser['website'], PHP_URL_HOST) ?: $profileUser['website']); ?>
                        </a>
                    </span>
                <?php endif; ?>
                
                <span class="meta-item">
                    <svg viewBox="0 0 24 24" class="meta-icon">
                        <g><path d="M7 4V3h2v1h6V3h2v1h1.5C19.89 4 21 5.12 21 6.5v12c0 1.38-1.11 2.5-2.5 2.5h-13C4.12 21 3 19.88 3 18.5v-12C3 5.12 4.12 4 5.5 4H7zm0 2H5.5c-.27 0-.5.22-.5.5v12c0 .28.23.5.5.5h13c.28 0 .5-.22.5-.5v-12c0-.28-.22-.5-.5-.5H17v1h-2V6H9v1H7V6zm0 6h2v-2H7v2zm0 4h2v-2H7v2zm4-4h2v-2h-2v2zm0 4h2v-2h-2v2zm4-4h2v-2h-2v2z"></path></g>
                    </svg>
                    Dołączył(a): <?php echo $joinDateFormatted; ?>
                </span>
            </div>

            <div class="profile-stats">
                <a href="#" class="stat">
                    <span class="stat-value"><?php echo formatNumber($profileUser['following_count']); ?></span>
                    <span class="stat-label">Obserwowani</span>
                </a>
                <a href="#" class="stat">
                    <span class="stat-value"><?php echo formatNumber($profileUser['followers_count']); ?></span>
                    <span class="stat-label">Obserwujący</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Nawigacja zakładek -->
    <nav class="profile-tabs">
        <a href="#" class="tab active" data-tab="posts">Posty</a>
        <a href="#" class="tab" data-tab="replies">Odpowiedzi</a>
        <a href="#" class="tab" data-tab="media">Media</a>
        <a href="#" class="tab" data-tab="likes">Polubienia</a>
    </nav>

    <!-- Zawartość zakładek -->
    <div class="tab-content active" id="posts-tab">
        <!-- Posty użytkownika będą ładowane przez JavaScript -->
        <div class="feed"></div>
    </div>
</main>

<?php require_once 'includes/right_sidebar.php'; ?>

<!-- Przekaż dane profilu jako atrybuty data -->
<div id="profile-data" 
     data-user-id="<?php echo $profileUser['id']; ?>" 
     data-username="<?php echo htmlspecialchars($profileUser['username']); ?>"
     data-is-own-profile="<?php echo $isOwnProfile ? 'true' : 'false'; ?>"
     style="display: none;">
</div>

<!-- Profile page only script -->
<script src="js/profile.js"></script>

<?php require_once 'includes/footer.php'; ?>