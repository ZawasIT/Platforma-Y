<?php
/**
 * API Endpoint: Pobieranie ostatnich postów dla sidebara
 * 
 * Parametry:
 * - limit: liczba postów do pobrania (domyślnie 3, max 10)
 */

require_once '../config.php';
require_once '../functions.php';

// Nagłówki
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Sprawdź autoryzację
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Wymagane logowanie']);
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];

// Pobierz limit z GET parametru
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
if ($limit < 1) $limit = 3;
if ($limit > 10) $limit = 10;

try {
    $sql = "
        SELECT 
            p.id,
            p.content,
            p.media_type,
            p.media_url,
            p.created_at,
            u.id as user_id,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified
        FROM posts p
        INNER JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT :limit
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatuj daty
    foreach ($posts as &$post) {
        $post['verified'] = (bool)$post['verified'];
    }
    
    echo json_encode($posts, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("API get_recent_posts error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Błąd serwera']);
}
