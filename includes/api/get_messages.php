<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

$userId = $_SESSION['user_id'];
$conversationId = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;

if ($conversationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe ID konwersacji']);
    exit;
}

try {
    // Sprawdź czy użytkownik jest uczestnikiem konwersacji
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM conversation_participants 
        WHERE conversation_id = ? AND user_id = ?
    ");
    $stmt->execute([$conversationId, $userId]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Brak dostępu do tej konwersacji']);
        exit;
    }
    
    // Pobiera wiadomości
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$conversationId]);
    $messages = $stmt->fetchAll();
    
    // Oznacz wiadomości jako przeczytane
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
    ");
    $stmt->execute([$conversationId, $userId]);
    
    // Pobiera informacje o drugim użytkowniku
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified
        FROM users u
        JOIN conversation_participants cp ON u.id = cp.user_id
        WHERE cp.conversation_id = ? AND cp.user_id != ?
    ");
    $stmt->execute([$conversationId, $userId]);
    $otherUser = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'other_user' => $otherUser
    ]);
    
} catch (PDOException $e) {
    error_log("Get messages error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas pobierania wiadomości'
    ]);
}
?>
