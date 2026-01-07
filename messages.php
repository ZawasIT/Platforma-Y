<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Sprawdza czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Pobiera dane zalogowanego użytkownika
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    header('Location: logout.php');
    exit;
}

$pageTitle = 'Wiadomości - Platforma Y';
$cssFile = 'css/style.css';

require_once 'includes/header.php';
?>

<?php require_once 'includes/left_sidebar.php'; ?>

<!-- Główna zawartość -->
<main class="main-content messages-page">
    <!-- Lista konwersacji -->
    <aside class="conversations-list">
        <div class="conversations-header">
            <h1>Wiadomości</h1>
            <button class="new-message-btn" title="Nowa wiadomość">
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <g><path d="M1.998 5.5c0-1.381 1.119-2.5 2.5-2.5h15c1.381 0 2.5 1.119 2.5 2.5v13c0 1.381-1.119 2.5-2.5 2.5h-15c-1.381 0-2.5-1.119-2.5-2.5v-13zm2.5-.5c-.276 0-.5.224-.5.5v2.764l8 3.638 8-3.636V5.5c0-.276-.224-.5-.5-.5h-15zm15.5 5.463l-8 3.636-8-3.638V18.5c0 .276.224.5.5.5h15c.276 0 .5-.224.5-.5v-8.037z"></path></g>
                </svg>
            </button>
        </div>
        <div class="conversations-container" id="conversations-container">
            <div class="loading">Ładowanie konwersacji...</div>
        </div>
    </aside>

    <!-- Panel czatu -->
    <div class="chat-panel" id="chat-panel">
        <div class="chat-empty">
            <svg viewBox="0 0 24 24" width="80" height="80" fill="var(--text-secondary)">
                <g><path d="M1.998 5.5c0-1.381 1.119-2.5 2.5-2.5h15c1.381 0 2.5 1.119 2.5 2.5v13c0 1.381-1.119 2.5-2.5 2.5h-15c-1.381 0-2.5-1.119-2.5-2.5v-13zm2.5-.5c-.276 0-.5.224-.5.5v2.764l8 3.638 8-3.636V5.5c0-.276-.224-.5-.5-.5h-15zm15.5 5.463l-8 3.636-8-3.638V18.5c0 .276.224.5.5.5h15c.276 0 .5-.224.5-.5v-8.037z"></path></g>
            </svg>
            <h2>Wybierz wiadomość</h2>
            <p>Wybierz z istniejących konwersacji lub rozpocznij nową</p>
        </div>
        
        <div class="chat-active" id="chat-active" style="display: none;">
            <div class="chat-header">
                <button class="back-btn" id="back-to-list">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <g><path d="M7.414 13l5.043 5.04-1.414 1.42L3.586 12l7.457-7.46 1.414 1.42L7.414 11H21v2H7.414z"></path></g>
                    </svg>
                </button>
                <img src="" alt="Avatar" class="chat-user-avatar" id="chat-user-avatar">
                <div class="chat-user-info">
                    <div class="chat-user-name" id="chat-user-name"></div>
                    <div class="chat-user-username" id="chat-user-username"></div>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <!-- Wiadomości będą ładowane tutaj -->
            </div>
            
            <div class="chat-input-container">
                <textarea 
                    id="message-input" 
                    placeholder="Napisz wiadomość..."
                    maxlength="1000"
                    rows="1"
                ></textarea>
                <button class="send-message-btn" id="send-message-btn">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <g><path d="M2.504 21.866l.526-2.108C3.04 19.73 4 15.35 4 12V8.414l8-4 8 4V12c0 3.35.96 7.73.97 7.758l.527 2.108-.53-.07c-.127-.017-3.395-.447-6.417-.447-3.027 0-6.296.43-6.428.446l-.523.07zM8 12.997c-.283 0-.5.22-.5.5s.217.5.5.5c.277 0 .5-.22.5-.5s-.223-.5-.5-.5zm4 0c-.287 0-.5.22-.5.5s.213.5.5.5c.283 0 .5-.22.5-.5s-.217-.5-.5-.5zm4 0c-.283 0-.5.22-.5.5s.217.5.5.5c.277 0 .5-.22.5-.5s-.223-.5-.5-.5z"></path></g>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/right_sidebar.php'; ?>

<div id="current-user-data" 
     data-user-id="<?php echo $currentUser['id']; ?>"
     data-username="<?php echo htmlspecialchars($currentUser['username']); ?>"
     style="display: none;">
</div>

<!-- Modal nowej wiadomości -->
<div class="modal" id="new-message-modal" style="display: none;">
    <div class="modal-content new-message-modal-content">
        <div class="modal-header">
            <h2>Nowa wiadomość</h2>
            <button class="modal-close-btn" id="close-new-message-modal">×</button>
        </div>
        <div class="modal-body">
            <input 
                type="text" 
                id="search-users-input" 
                placeholder="Wyszukaj użytkownika..."
                class="search-input"
            >
            <div class="users-search-results" id="users-search-results">
                <!-- Wyniki wyszukiwania użytkowników -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Messages !== 'undefined') {
            Messages.init();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
