<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Sprawdza czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Musisz być zalogowany']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda']);
    exit;
}

$userId = $_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');

// Obsługa uploadu zdjęcia
$mediaType = 'none';
$mediaUrl = null;
$hasImage = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK;

// Walidacja - post musi mieć tekst lub zdjęcie
if (empty($content) && !$hasImage) {
    echo json_encode(['success' => false, 'message' => 'Post nie może być pusty']);
    exit;
}

if (!empty($content) && mb_strlen($content) > 280) {
    echo json_encode(['success' => false, 'message' => 'Post może mieć maksymalnie 280 znaków']);
    exit;
}

if ($hasImage) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $fileType = $_FILES['image']['type'];
    $fileSize = $_FILES['image']['size'];
    
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Nieprawidłowy typ pliku. Dozwolone: JPG, PNG, GIF, WebP']);
        exit;
    }
    
    if ($fileSize > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Plik jest za duży. Maksymalny rozmiar: 5MB']);
        exit;
    }
    
    // Utwórz folder uploads jeśli nie istnieje
    $uploadDir = __DIR__ . '/../uploads/posts/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generuj unikalną nazwę pliku
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('post_', true) . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
        $mediaType = ($fileType === 'image/gif') ? 'gif' : 'image';
        $mediaUrl = 'uploads/posts/' . $fileName;
    } else {
        error_log("Failed to move uploaded file to: " . $filePath);
        echo json_encode(['success' => false, 'message' => 'Nie udało się przesłać pliku']);
        exit;
    }
}

try {
    // Tworzy post z mediami
    $stmt = $pdo->prepare("
        INSERT INTO posts (user_id, content, media_type, media_url, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $content, $mediaType, $mediaUrl]);
    
    $postId = $pdo->lastInsertId();
    
    // Pobiera utworzony post z danymi użytkownika
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            u.username,
            u.full_name,
            u.profile_image,
            u.verified
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Post dodany pomyślnie',
        'post' => $post
    ]);
    
} catch (PDOException $e) {
    error_log("Create post error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Wystąpił błąd podczas tworzenia posta'
    ]);
}
?>
