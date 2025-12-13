<?php
    require_once 'includes/config.php';
    require_once 'includes/functions.php';

    // Sprawdza czy użytkownik jest zalogowany
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    // Pobiera dane zalogowanego użytkownika dla lewego sidebara
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();

    // Pobiera sugestie użytkowników dla prawego sidebara
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

    // Pobiera i sanitizuje zapytanie
    $query = isset($_GET['q']) ? clean($_GET['q']) : '';

    // Ustawienia dla nagłówka
    $pageTitle = 'Wyniki wyszukiwania dla: ' . htmlspecialchars($query) . ' / Platforma Y';
    $cssFile = 'css/style.css'; // Używamy głównego stylesheet
    
    require_once 'includes/header.php';
?>

<?php require_once 'includes/left_sidebar.php'; ?>

<!-- Główna zawartość -->
<main class="main-content">
    <!-- Nagłówek dla wyszukiwania -->
    <header class="feed-header" style="display: block; padding: 12px 16px;">
        <h2 style="font-size: 20px;">Wyniki dla "<?php echo htmlspecialchars($query); ?>"</h2>
        <p style="font-size: 13px; color: var(--text-secondary);">Wyszukaj posty i użytkowników na Platformie Y</p>
    </header>

    <div class="feed-tabs">
        <button class="feed-tab search-tab active" data-tab="popular">Popularne</button>
        <button class="feed-tab search-tab" data-tab="latest">Ostatnie</button>
        <button class="feed-tab search-tab" data-tab="users">Użytkownicy</button>
    </div>

    <!-- Kontener na wyniki -->
    <div id="search-results" class="feed">
        <!-- Wyniki będą ładowane dynamicznie przez JavaScript -->
        <div class="loading">Ładowanie...</div>
    </div>
</main>

<?php require_once 'includes/right_sidebar.php'; ?>

<!-- Ukryty div do przekazania zapytania do JS -->
<div id="search-query" data-query="<?php echo htmlspecialchars($query); ?>" style="display: none;"></div>

<?php 
// Buforowanie wyjścia stopki, aby wstrzyknąć skrypt search.js w poprawne miejsce (przed </body>)
ob_start();
require_once 'includes/footer.php'; 
$footerContent = ob_get_clean();
echo str_replace('</body>', '<script src="js/search.js?v=' . time() . '"></script></body>', $footerContent);
?>
