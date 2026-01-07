// js/modules/notifications.js

// Moduł powiadomień
const Notifications = {
    notificationsPanel: null,
    notificationsList: null,
    notificationBadge: null,
    notificationCount: null,
    unreadCount: 0,
    
    init() {
        this.createNotificationsPanel();
        this.setupEventListeners();
        this.fetchNotifications();
        
        // Aktualizuj powiadomienia co 30 sekund
        setInterval(() => this.fetchNotifications(), 30000);
    },
    
    createNotificationsPanel() {
        // Tworzy panel powiadomień
        const panel = document.createElement('div');
        panel.id = 'notifications-panel';
        panel.className = 'notifications-panel';
        panel.style.display = 'none';
        panel.innerHTML = `
            <div class="notifications-header">
                <h2>Powiadomienia</h2>
                <button class="mark-all-read-btn" title="Oznacz wszystkie jako przeczytane">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <g><path d="M9 20c-.264 0-.52-.104-.707-.293l-4.785-4.785c-.39-.39-.39-1.023 0-1.414s1.023-.39 1.414 0l3.946 3.945L18.075 4.41c.32-.446.94-.544 1.385-.223.445.32.544.939.223 1.385L9.88 19.367c-.15.21-.359.36-.596.41-.078.016-.157.022-.236.022z"></path></g>
                    </svg>
                </button>
                <button class="close-panel-btn">×</button>
            </div>
            <div class="notifications-list" id="notifications-list">
                <div class="loading">Ładowanie...</div>
            </div>
        `;
        document.body.appendChild(panel);
        
        this.notificationsPanel = panel;
        this.notificationsList = panel.querySelector('#notifications-list');
        this.notificationBadge = document.getElementById('notification-count');
    },
    
    setupEventListeners() {
        // Przycisk powiadomień w nawigacji
        const notificationsLink = document.getElementById('notifications-link');
        if (notificationsLink) {
            notificationsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.togglePanel();
            });
        }
        
        // Przycisk zamknięcia panelu
        const closeBtn = this.notificationsPanel.querySelector('.close-panel-btn');
        closeBtn.addEventListener('click', () => this.hidePanel());
        
        // Przycisk "oznacz wszystkie jako przeczytane"
        const markAllBtn = this.notificationsPanel.querySelector('.mark-all-read-btn');
        markAllBtn.addEventListener('click', () => this.markAllAsRead());
        
        // Zamknij panel po kliknięciu poza nim
        document.addEventListener('click', (e) => {
            if (this.notificationsPanel.style.display === 'block' &&
                !this.notificationsPanel.contains(e.target) &&
                !document.getElementById('notifications-link').contains(e.target)) {
                this.hidePanel();
            }
        });
    },
    
    togglePanel() {
        if (this.notificationsPanel.style.display === 'none') {
            this.showPanel();
        } else {
            this.hidePanel();
        }
    },
    
    showPanel() {
        this.notificationsPanel.style.display = 'block';
        this.fetchNotifications();
    },
    
    hidePanel() {
        this.notificationsPanel.style.display = 'none';
    },
    
    async fetchNotifications() {
        try {
            const response = await fetch('includes/api/get_notifications.php');
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.unread_count;
                this.updateBadge();
                this.renderNotifications(data.notifications);
            } else {
                console.error('Błąd pobierania powiadomień:', data.message);
            }
        } catch (error) {
            console.error('Błąd:', error);
        }
    },
    
    updateBadge() {
        if (this.notificationBadge) {
            if (this.unreadCount > 0) {
                this.notificationBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                this.notificationBadge.style.display = 'flex';
            } else {
                this.notificationBadge.style.display = 'none';
            }
        }
    },
    
    renderNotifications(notifications) {
        if (!notifications || notifications.length === 0) {
            this.notificationsList.innerHTML = '<div class="no-notifications">Brak powiadomień</div>';
            return;
        }
        
        this.notificationsList.innerHTML = notifications.map(notif => 
            this.createNotificationHTML(notif)
        ).join('');
        
        // Dodaj event listenery do powiadomień
        this.notificationsList.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const notifId = item.dataset.notificationId;
                const isRead = item.dataset.isRead === '1';
                
                if (!isRead) {
                    this.markAsRead(notifId);
                }
                
                // Opcjonalnie: przekieruj do posta
                const postId = item.dataset.postId;
                if (postId) {
                    // window.location.href = `post.php?id=${postId}`;
                }
            });
        });
    },
    
    createNotificationHTML(notif) {
        const timeAgo = this.formatTimeAgo(notif.created_at);
        const isRead = notif.is_read == 1;
        const readClass = isRead ? 'read' : 'unread';
        
        let icon = '';
        let message = '';
        
        switch (notif.type) {
            case 'like':
                icon = '<svg viewBox="0 0 24 24" fill="#f91880"><g><path d="M12 21.638h-.014C9.403 21.59 1.95 14.856 1.95 8.478c0-3.064 2.525-5.754 5.403-5.754 2.29 0 3.83 1.58 4.646 2.73.814-1.148 2.354-2.73 4.645-2.73 2.88 0 5.404 2.69 5.404 5.755 0 6.376-7.454 13.11-10.037 13.157H12z"></path></g></svg>';
                message = `<strong>${notif.actor_full_name}</strong> polubił(a) Twój post`;
                break;
            case 'reply':
                icon = '<svg viewBox="0 0 24 24" fill="#1d9bf0"><g><path d="M1.751 10c0-4.42 3.584-8 8.005-8h4.366c4.49 0 8.129 3.64 8.129 8.13 0 2.96-1.607 5.68-4.196 7.11l-8.054 4.46v-3.69h-.067c-4.49.1-8.183-3.51-8.183-8.01zm8.005-6c-3.317 0-6.005 2.69-6.005 6 0 3.37 2.77 6.08 6.138 6.01l.351-.01h1.761v2.3l5.087-2.81c1.951-1.08 3.163-3.13 3.163-5.36 0-3.39-2.744-6.13-6.129-6.13H9.756z"></path></g></svg>';
                message = `<strong>${notif.actor_full_name}</strong> odpowiedział(a) na Twój post`;
                break;
            case 'follow':
                icon = '<svg viewBox="0 0 24 24" fill="#1d9bf0"><g><path d="M17.863 13.44c1.477 1.58 2.366 3.8 2.632 6.46l.11 1.1H3.395l.11-1.1c.266-2.66 1.155-4.88 2.632-6.46C7.627 11.85 9.648 11 12 11s4.373.85 5.863 2.44zM12 2C9.791 2 8 3.79 8 6s1.791 4 4 4 4-1.79 4-4-1.791-4-4-4z"></path></g></svg>';
                message = `<strong>${notif.actor_full_name}</strong> zaczął(ęła) Cię obserwować`;
                break;
            default:
                icon = '<svg viewBox="0 0 24 24" fill="#1d9bf0"><g><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path></g></svg>';
                message = `<strong>${notif.actor_full_name}</strong> - ${notif.type}`;
        }
        
        return `
            <div class="notification-item ${readClass}" 
                 data-notification-id="${notif.id}"
                 data-post-id="${notif.post_id || ''}"
                 data-is-read="${notif.is_read}">
                <div class="notification-content">
                    <div class="notification-avatar">
                        <img src="${notif.actor_profile_image}" alt="${notif.actor_username}">
                        <div class="notification-icon">${icon}</div>
                    </div>
                    <div class="notification-text">
                        <p>${message}</p>
                        ${notif.post_content ? `<span class="notification-post-preview">${this.truncateText(notif.post_content, 50)}</span>` : ''}
                        <span class="notification-time">${timeAgo}</span>
                    </div>
                </div>
                ${!isRead ? '<div class="notification-unread-dot"></div>' : ''}
            </div>
        `;
    },
    
    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            
            const response = await fetch('includes/api/mark_notification_read.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Usuń powiadomienie z UI z animacją
                const notifElement = this.notificationsList.querySelector(
                    `[data-notification-id="${notificationId}"]`
                );
                if (notifElement) {
                    notifElement.style.opacity = '0';
                    notifElement.style.transform = 'translateX(100%)';
                    notifElement.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        notifElement.remove();
                        
                        // Sprawdź czy są jeszcze jakieś powiadomienia
                        const remaining = this.notificationsList.querySelectorAll('.notification-item');
                        if (remaining.length === 0) {
                            this.notificationsList.innerHTML = '<div class="no-notifications">Brak powiadomień</div>';
                        }
                    }, 300);
                }
                
                this.unreadCount--;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Błąd oznaczania jako przeczytane:', error);
        }
    },
    
    async markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('notification_id', '0'); // 0 oznacza wszystkie
            
            const response = await fetch('includes/api/mark_notification_read.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Usuń wszystkie powiadomienia z UI z animacją
                const allNotifications = this.notificationsList.querySelectorAll('.notification-item');
                
                allNotifications.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(100%)';
                        item.style.transition = 'all 0.3s ease';
                    }, index * 50);
                });
                
                setTimeout(() => {
                    this.notificationsList.innerHTML = '<div class="no-notifications">Brak powiadomień</div>';
                }, allNotifications.length * 50 + 300);
                
                this.unreadCount = 0;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Błąd oznaczania wszystkich jako przeczytane:', error);
        }
    },
    
    formatTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diff = Math.floor((now - time) / 1000); // różnica w sekundach
        
        if (diff < 60) return 'teraz';
        if (diff < 3600) return `${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d`;
        
        return time.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' });
    },
    
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
};

// Eksportuj dla użycia w innych plikach
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Notifications;
}
