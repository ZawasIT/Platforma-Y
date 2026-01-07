<?php
// includes/api/delete_conversation.php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda']);
    exit;
}

$userId = $_SESSION['user_id'];
$conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;

if ($conversationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe ID konwersacji']);
    exit;
}

try {
    // Sprawdź czy użytkownik należy do konwersacji
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM conversation_participants 
        WHERE conversation_id = ? AND user_id = ?
    ");
    $stmt->execute([$conversationId, $userId]);
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Brak dostępu do tej konwersacji']);
        exit;
    }
    
    // Usuń wiadomości z konwersacji
    $stmt = $pdo->prepare("DELETE FROM messages WHERE conversation_id = ?");
    $stmt->execute([$conversationId]);
    
    // Usuń uczestników konwersacji
    $stmt = $pdo->prepare("DELETE FROM conversation_participants WHERE conversation_id = ?");
    $stmt->execute([$conversationId]);
    
    // Usuń konwersację
    $stmt = $pdo->prepare("DELETE FROM conversations WHERE id = ?");
    $stmt->execute([$conversationId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Konwersacja została usunięta'
    ]);
    
} catch (PDOException $e) {
    error_log("Błąd usuwania konwersacji: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas usuwania konwersacji'
    ]);
}
