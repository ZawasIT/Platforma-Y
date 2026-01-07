<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

// Sprawdza czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

// Sprawdza czy to POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda']);
    exit;
}

$userId = $_SESSION['user_id'];
$notificationId = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;

try {
    if ($notificationId > 0) {
        // Oznacza konkretne powiadomienie jako przeczytane
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notificationId, $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Powiadomienie oznaczone jako przeczytane'
        ]);
    } else {
        // Oznacza wszystkie powiadomienia jako przeczytane
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Wszystkie powiadomienia oznaczone jako przeczytane'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Mark notification read error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas aktualizacji powiadomienia'
    ]);
}
?>
