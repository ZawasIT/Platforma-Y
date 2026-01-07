<?php
require_once '../config.php';
require_once '../functions.php';

// Włącz wyświetlanie błędów dla debugowania
error_reporting(E_ALL);
ini_set('display_errors', 0); // Nie pokazuj bezpośrednio, tylko loguj

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
$otherUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($otherUserId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe ID użytkownika']);
    exit;
}

if ($otherUserId == $userId) {
    echo json_encode(['success' => false, 'message' => 'Nie możesz rozpocząć konwersacji sam ze sobą']);
    exit;
}

try {
    // Sprawdź czy tabele istnieją
    $stmt = $pdo->query("SHOW TABLES LIKE 'conversations'");
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'System wiadomości nie jest skonfigurowany. Uruchom plik database/create_messages_tables.sql']);
        exit;
    }
    
    // Sprawdź czy użytkownik istnieje
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$otherUserId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Użytkownik nie istnieje']);
        exit;
    }
    
    // Sprawdź czy konwersacja już istnieje
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM conversations c
        JOIN conversation_participants cp1 ON c.id = cp1.conversation_id AND cp1.user_id = ?
        JOIN conversation_participants cp2 ON c.id = cp2.conversation_id AND cp2.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId, $otherUserId]);
    $existingConversation = $stmt->fetch();
    
    if ($existingConversation) {
        echo json_encode([
            'success' => true,
            'conversation_id' => $existingConversation['id']
        ]);
        exit;
    }
    
    // Utwórz nową konwersację
    $stmt = $pdo->prepare("INSERT INTO conversations (created_at) VALUES (NOW())");
    $stmt->execute();
    
    $conversationId = $pdo->lastInsertId();
    
    // Dodaj uczestników
    $stmt = $pdo->prepare("
        INSERT INTO conversation_participants (conversation_id, user_id) 
        VALUES (?, ?), (?, ?)
    ");
    $stmt->execute([$conversationId, $userId, $conversationId, $otherUserId]);
    
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversationId
    ]);
    
} catch (PDOException $e) {
    error_log("Create conversation error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd bazy danych. Sprawdź czy tabele wiadomości zostały utworzone.',
        'error' => $e->getMessage() // Tymczasowo dla debugowania
    ]);
}
?>
