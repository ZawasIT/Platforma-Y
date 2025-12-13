<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../functions.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Brak uwierzytelnienia']);
    http_response_code(401);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popular'; // Domyślnie 'popular'

// Jeśli zapytanie jest puste, zwróć pustą tablicę
if (empty($query)) {
    echo json_encode([]);
    exit;
}

$searchQuery = '%' . $query . '%';

// Bezpieczna walidacja parametru sortowania
$allowedSorts = [
    'popular' => 'p.likes_count DESC, p.created_at DESC',
    'latest' => 'p.created_at DESC'
];

$orderBy = $allowedSorts[$sort] ?? $allowedSorts['latest']; // Domyślnie 'latest' jeśli podano nieprawidłową wartość

try {
    // Przygotowanie zapytania SQL
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.user_id,
            p.content,
            p.created_at,
            p.likes_count,
            p.replies_count,
            p.reposts_count,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = :current_user_id_1) as is_liked,
            EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_id = :current_user_id_2) as is_reposted
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.content LIKE :search_query_1 OR u.username LIKE :search_query_2 OR u.full_name LIKE :search_query_3
        ORDER BY $orderBy
        LIMIT 100
    ");

    // Wykonanie zapytania
    $stmt->execute([
        'current_user_id_1' => $currentUserId,
        'current_user_id_2' => $currentUserId,
        'search_query_1' => $searchQuery,
        'search_query_2' => $searchQuery,
        'search_query_3' => $searchQuery
    ]);

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatowanie daty dla każdego posta
    foreach ($posts as &$post) {
        $post['time_ago'] = timeAgo($post['created_at']);
    }

    echo json_encode($posts);

} catch (PDOException $e) {
    // Logowanie błędu do pliku z logami serwera
    error_log("Błąd API wyszukiwania postów: " . $e->getMessage());

    // Wysłanie ogólnej odpowiedzi o błędzie
    http_response_code(500);
    echo json_encode(['error' => 'Błąd bazy danych: ' . $e->getMessage()]);
}
?>
