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

// Pobiera dane z formularza
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$website = isset($_POST['website']) ? trim($_POST['website']) : '';

// Obsługa uploadów zdjęć
$profileImage = null;
$bannerImage = null;

// Upload zdjęcia profilowego
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
        $errors[] = 'Zdjęcie profilowe musi być w formacie JPG, PNG, GIF lub WebP';
    } elseif ($_FILES['profile_image']['size'] > $maxSize) {
        $errors[] = 'Zdjęcie profilowe nie może być większe niż 5MB';
    } else {
        $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $profileImage = 'uploads/profiles/' . $filename;
        } else {
            $errors[] = 'Błąd podczas przesyłania zdjęcia profilowego';
        }
    }
}

// Upload zdjęcia bannera
if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/banners/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['banner_image']['type'], $allowedTypes)) {
        $errors[] = 'Zdjęcie tła musi być w formacie JPG, PNG, GIF lub WebP';
    } elseif ($_FILES['banner_image']['size'] > $maxSize) {
        $errors[] = 'Zdjęcie tła nie może być większe niż 5MB';
    } else {
        $extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
        $filename = 'banner_' . $userId . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $targetPath)) {
            $bannerImage = 'uploads/banners/' . $filename;
        } else {
            $errors[] = 'Błąd podczas przesyłania zdjęcia tła';
        }
    }
}

// Walidacja
$errors = [];

if (empty($fullName)) {
    $errors[] = 'Imię i nazwisko jest wymagane';
} elseif (mb_strlen($fullName) > 50) {
    $errors[] = 'Imię i nazwisko może mieć maksymalnie 50 znaków';
}

if (mb_strlen($bio) > 160) {
    $errors[] = 'Bio może mieć maksymalnie 160 znaków';
}

if (mb_strlen($location) > 30) {
    $errors[] = 'Lokalizacja może mieć maksymalnie 30 znaków';
}

if (!empty($website) && mb_strlen($website) > 100) {
    $errors[] = 'Strona internetowa może mieć maksymalnie 100 znaków';
}

// Walidacja URL
if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
    $errors[] = 'Podaj prawidłowy adres URL';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Buduj zapytanie SQL dynamicznie
    $updateFields = [
        'full_name = ?',
        'bio = ?',
        'location = ?',
        'website = ?'
    ];
    $params = [$fullName, $bio, $location, $website];
    
    if ($profileImage !== null) {
        $updateFields[] = 'profile_image = ?';
        $params[] = $profileImage;
    }
    
    if ($bannerImage !== null) {
        $updateFields[] = 'banner_image = ?';
        $params[] = $bannerImage;
    }
    
    $params[] = $userId;
    
    // Aktualizuje profil w bazie danych
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Pobiera zaktualizowane dane
    $stmt = $pdo->prepare("SELECT full_name, bio, location, website, profile_image, banner_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profil został zaktualizowany',
        'user' => $updatedUser
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Błąd podczas aktualizacji profilu'
    ]);
}
