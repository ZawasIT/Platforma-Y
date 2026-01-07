<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

// Sprawdza czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Pobiera powiadomienia użytkownika wraz z danymi aktora
    $stmt = $pdo->prepare("
        SELECT 
            n.*,
            u.username as actor_username,
            u.full_name as actor_full_name,
            u.profile_image as actor_profile_image,
            u.verified as actor_verified,
            p.content as post_content
        FROM notifications n
        JOIN users u ON n.actor_id = u.id
        LEFT JOIN posts p ON n.post_id = p.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();
    
    // Pobiera liczbę nieprzeczytanych powiadomień
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);
    $unreadCount = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => (int)$unreadCount
    ]);
    
} catch (PDOException $e) {
    error_log("Get notifications error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas pobierania powiadomień'
    ]);
}
?>
