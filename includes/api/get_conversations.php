<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Pobiera wszystkie konwersacje użytkownika z ostatnią wiadomością
    $stmt = $pdo->prepare("
        SELECT 
            c.id as conversation_id,
            c.updated_at,
            u.id as other_user_id,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified,
            m.content as last_message,
            m.sender_id as last_sender_id,
            m.created_at as last_message_time,
            (SELECT COUNT(*) 
             FROM messages 
             WHERE conversation_id = c.id 
             AND sender_id != ? 
             AND is_read = 0) as unread_count
        FROM conversations c
        JOIN conversation_participants cp1 ON c.id = cp1.conversation_id AND cp1.user_id = ?
        JOIN conversation_participants cp2 ON c.id = cp2.conversation_id AND cp2.user_id != ?
        JOIN users u ON cp2.user_id = u.id
        LEFT JOIN messages m ON m.id = (
            SELECT id FROM messages 
            WHERE conversation_id = c.id 
            ORDER BY created_at DESC 
            LIMIT 1
        )
        ORDER BY c.updated_at DESC
    ");
    $stmt->execute([$userId, $userId, $userId]);
    $conversations = $stmt->fetchAll();
    
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
        'conversations' => $conversations,
        'total_unread' => (int)$totalUnread
    ]);
    
} catch (PDOException $e) {
    error_log("Get conversations error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas pobierania konwersacji'
    ]);
}
?>
