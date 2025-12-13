<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../functions.php';

// Sprawdza, czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    http_response_code(401);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$searchQuery = '%' . $query . '%';

try {
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            username, 
            full_name, 
            bio, 
            profile_image, 
            verified,
            (SELECT COUNT(*) FROM follows WHERE following_id = users.id) as followers_count,
            EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = users.id) as is_following
        FROM users
        WHERE username LIKE ? OR full_name LIKE ?
        ORDER BY followers_count DESC
        LIMIT 50
    ");

    $stmt->execute([$currentUserId, $searchQuery, $searchQuery]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);

} catch (PDOException $e) {
    // Log error
    error_log("Search users API error: " . $e->getMessage());
    // Send generic error response
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
