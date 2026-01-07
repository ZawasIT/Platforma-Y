<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'unread_count' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Sprawdź czy tabele istnieją
    $stmt = $pdo->query("SHOW TABLES LIKE 'messages'");
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => true, 'unread_count' => 0]);
        exit;
    }
    
    // Oblicz całkowitą liczbę nieprzeczytanych wiadomości
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_unread
        FROM messages m
        JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
        WHERE cp.user_id = ? AND m.sender_id != ? AND m.is_read = 0
    ");
    $stmt->execute([$userId, $userId]);
    $totalUnread = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'unread_count' => (int)$totalUnread
    ]);
    
} catch (PDOException $e) {
    error_log("Get unread messages count error: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'unread_count' => 0
    ]);
}
?>
