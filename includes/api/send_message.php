<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda']);
    exit;
}

$userId = $_SESSION['user_id'];
$conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($conversationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe ID konwersacji']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Wiadomość nie może być pusta']);
    exit;
}

if (mb_strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Wiadomość może mieć maksymalnie 1000 znaków']);
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
    
    // Dodaje wiadomość
    $stmt = $pdo->prepare("
        INSERT INTO messages (conversation_id, sender_id, content, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$conversationId, $userId, $content]);
    
    $messageId = $pdo->lastInsertId();
    
    // Pobiera dodaną wiadomość z danymi użytkownika
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (PDOException $e) {
    error_log("Send message error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas wysyłania wiadomości'
    ]);
}
?>
