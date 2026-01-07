// js/modules/messages.js

const Messages = {
    conversationsContainer: null,
    chatPanel: null,
    chatActive: null,
    chatMessages: null,
    messageInput: null,
    currentConversationId: null,
    currentOtherUser: null,
    conversations: [],
    refreshInterval: null,
    
    init() {
        this.conversationsContainer = document.getElementById('conversations-container');
        this.chatPanel = document.getElementById('chat-panel');
        this.chatActive = document.getElementById('chat-active');
        this.chatMessages = document.getElementById('chat-messages');
        this.messageInput = document.getElementById('message-input');
        
        if (!this.conversationsContainer) return;
        
        this.setupEventListeners();
        this.loadConversations();
        
        // Sprawdź czy jest parametr conversation w URL
        const urlParams = new URLSearchParams(window.location.search);
        const conversationId = urlParams.get('conversation');
        if (conversationId) {
            // Poczekaj na załadowanie konwersacji, a potem otwórz
            setTimeout(() => {
                this.openConversation(parseInt(conversationId));
            }, 500);
        }
        
        // Odśwież konwersacje co 10 sekund
        this.refreshInterval = setInterval(() => {
            this.loadConversations();
            if (this.currentConversationId) {
                this.loadMessages(this.currentConversationId, false);
            }
        }, 10000);
    },
    
    setupEventListeners() {
        // Przycisk nowej wiadomości
        const newMessageBtn = document.querySelector('.new-message-btn');
        if (newMessageBtn) {
            newMessageBtn.addEventListener('click', () => this.showNewMessageModal());
        }
        
        // Przycisk wysyłania wiadomości
        const sendBtn = document.getElementById('send-message-btn');
        if (sendBtn) {
            sendBtn.addEventListener('click', () => this.sendMessage());
        }
        
        // Enter w textarea wysyła wiadomość
        if (this.messageInput) {
            this.messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            // Auto-resize textarea
            this.messageInput.addEventListener('input', () => {
                this.messageInput.style.height = 'auto';
                this.messageInput.style.height = this.messageInput.scrollHeight + 'px';
            });
        }
        
        // Przycisk powrotu do listy
        const backBtn = document.getElementById('back-to-list');
        if (backBtn) {
            backBtn.addEventListener('click', () => this.showConversationsList());
        }
        
        // Modal nowej wiadomości
        const closeModalBtn = document.getElementById('close-new-message-modal');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => this.hideNewMessageModal());
        }
        
        // Wyszukiwanie użytkowników
        const searchInput = document.getElementById('search-users-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchUsers(e.target.value);
                }, 300);
            });
        }
    },
    
    async loadConversations() {
        try {
            const response = await fetch('includes/api/get_conversations.php');
            const data = await response.json();
            
            if (data.success) {
                this.conversations = data.conversations;
                this.renderConversations(data.conversations);
                this.updateMessagesCount(data.total_unread);
            } else {
                console.error('Błąd:', data.message);
            }
        } catch (error) {
            console.error('Błąd:', error);
        }
    },
    
    renderConversations(conversations) {
        if (!conversations || conversations.length === 0) {
            this.conversationsContainer.innerHTML = `
                <div class="no-conversations">
                    <p>Brak konwersacji</p>
                    <p class="text-secondary">Rozpocznij nową konwersację klikając przycisk powyżej</p>
                </div>
            `;
            return;
        }
        
        this.conversationsContainer.innerHTML = conversations.map(conv => 
            this.createConversationHTML(conv)
        ).join('');
        
        // Dodaj event listenery
        this.conversationsContainer.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', () => {
                const convId = parseInt(item.dataset.conversationId);
                this.openConversation(convId);
            });
        });
    },
    
    createConversationHTML(conv) {
        const timeAgo = this.formatTimeAgo(conv.updated_at);
        const isYou = conv.last_sender_id == document.getElementById('current-user-data').dataset.userId;
        const lastMessagePreview = conv.last_message ? 
            (isYou ? 'Ty: ' : '') + this.truncateText(conv.last_message, 40) : 
            'Rozpocznij konwersację';
        
        const unreadBadge = conv.unread_count > 0 ? 
            `<span class="conversation-unread-badge">${conv.unread_count}</span>` : '';
        
        const activeClass = conv.conversation_id == this.currentConversationId ? 'active' : '';
        
        return `
            <div class="conversation-item ${activeClass}" data-conversation-id="${conv.conversation_id}">
                <img src="${conv.profile_image}" alt="${conv.username}" class="conversation-avatar">
                <div class="conversation-info">
                    <div class="conversation-header">
                        <span class="conversation-name">
                            ${conv.full_name}
                            ${conv.verified ? '<svg viewBox="0 0 24 24" class="verified-badge-small"><g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g></svg>' : ''}
                        </span>
                        <span class="conversation-time">${timeAgo}</span>
                    </div>
                    <div class="conversation-last-message">
                        ${lastMessagePreview}
                        ${unreadBadge}
                    </div>
                </div>
            </div>
        `;
    },
    
    async openConversation(conversationId) {
        this.currentConversationId = conversationId;
        
        // Znajdź konwersację w liście
        const conv = this.conversations.find(c => c.conversation_id == conversationId);
        if (conv) {
            this.currentOtherUser = {
                id: conv.other_user_id,
                username: conv.username,
                full_name: conv.full_name,
                profile_image: conv.profile_image,
                verified: conv.verified
            };
        }
        
        // Pokaż panel czatu
        this.showChatPanel();
        
        // Załaduj wiadomości
        await this.loadMessages(conversationId);
        
        // Zaktualizuj aktywną konwersację w liście
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.toggle('active', parseInt(item.dataset.conversationId) == conversationId);
        });
    },
    
    async loadMessages(conversationId, scrollToBottom = true) {
        try {
            const response = await fetch(`includes/api/get_messages.php?conversation_id=${conversationId}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentOtherUser = data.other_user;
                this.renderMessages(data.messages, scrollToBottom);
                this.updateChatHeader(data.other_user);
            }
        } catch (error) {
            console.error('Błąd:', error);
        }
    },
    
    renderMessages(messages, scrollToBottom = true) {
        const currentUserId = document.getElementById('current-user-data').dataset.userId;
        
        if (!messages || messages.length === 0) {
            this.chatMessages.innerHTML = `
                <div class="no-messages">
                    <p>Brak wiadomości</p>
                    <p class="text-secondary">Rozpocznij konwersację!</p>
                </div>
            `;
            return;
        }
        
        this.chatMessages.innerHTML = messages.map(msg => {
            const isOwn = msg.sender_id == currentUserId;
            const time = this.formatMessageTime(msg.created_at);
            
            return `
                <div class="message ${isOwn ? 'message-own' : 'message-other'}">
                    ${!isOwn ? `<img src="${msg.profile_image}" alt="${msg.username}" class="message-avatar">` : ''}
                    <div class="message-content">
                        <div class="message-bubble">${this.escapeHtml(msg.content)}</div>
                        <div class="message-time">${time}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        if (scrollToBottom) {
            this.scrollToBottom();
        }
    },
    
    async sendMessage() {
        const content = this.messageInput.value.trim();
        
        if (!content || !this.currentConversationId) return;
        
        try {
            const formData = new FormData();
            formData.append('conversation_id', this.currentConversationId);
            formData.append('content', content);
            
            const response = await fetch('includes/api/send_message.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.messageInput.value = '';
                this.messageInput.style.height = 'auto';
                await this.loadMessages(this.currentConversationId);
                await this.loadConversations();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Błąd:', error);
        }
    },
    
    showChatPanel() {
        document.querySelector('.chat-empty').style.display = 'none';
        this.chatActive.style.display = 'flex';
        
        // Na mobile ukryj listę konwersacji
        if (window.innerWidth <= 700) {
            document.querySelector('.conversations-list').style.display = 'none';
        }
    },
    
    showConversationsList() {
        if (window.innerWidth <= 700) {
            document.querySelector('.conversations-list').style.display = 'flex';
            this.chatActive.style.display = 'none';
            document.querySelector('.chat-empty').style.display = 'flex';
        }
    },
    
    updateChatHeader(user) {
        document.getElementById('chat-user-avatar').src = user.profile_image;
        document.getElementById('chat-user-name').innerHTML = user.full_name + 
            (user.verified ? ' <svg viewBox="0 0 24 24" class="verified-badge-small"><g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g></svg>' : '');
        document.getElementById('chat-user-username').textContent = '@' + user.username;
    },
    
    updateMessagesCount(count) {
        const badge = document.getElementById('messages-count');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    },
    
    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    },
    
    // Modal nowej wiadomości
    showNewMessageModal() {
        document.getElementById('new-message-modal').style.display = 'flex';
        document.getElementById('search-users-input').focus();
    },
    
    hideNewMessageModal() {
        document.getElementById('new-message-modal').style.display = 'none';
        document.getElementById('search-users-input').value = '';
        document.getElementById('users-search-results').innerHTML = '';
    },
    
    async searchUsers(query) {
        if (!query || query.length < 2) {
            document.getElementById('users-search-results').innerHTML = '';
            return;
        }
        
        try {
            const response = await fetch(`includes/api/search_users.php?q=${encodeURIComponent(query)}`);
            const users = await response.json();
            
            if (users && Array.isArray(users)) {
                this.renderSearchResults(users);
            } else {
                document.getElementById('users-search-results').innerHTML = '<div class="no-results">Nie znaleziono użytkowników</div>';
            }
        } catch (error) {
            console.error('Błąd:', error);
            document.getElementById('users-search-results').innerHTML = '<div class="no-results">Błąd wyszukiwania</div>';
        }
    },
    
    renderSearchResults(users) {
        const resultsContainer = document.getElementById('users-search-results');
        
        if (!users || users.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">Nie znaleziono użytkowników</div>';
            return;
        }
        
        resultsContainer.innerHTML = users.map(user => `
            <div class="user-search-item" data-user-id="${user.id}">
                <img src="${user.profile_image}" alt="${user.username}" class="user-search-avatar">
                <div class="user-search-info">
                    <div class="user-search-name">
                        ${user.full_name}
                        ${user.verified ? '<svg viewBox="0 0 24 24" class="verified-badge-small"><g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g></svg>' : ''}
                    </div>
                    <div class="user-search-username">@${user.username}</div>
                </div>
            </div>
        `).join('');
        
        // Dodaj event listenery
        resultsContainer.querySelectorAll('.user-search-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = item.dataset.userId;
                this.createConversation(userId);
            });
        });
    },
    
    async createConversation(userId) {
        try {
            const formData = new FormData();
            formData.append('user_id', userId);
            
            const response = await fetch('includes/api/create_conversation.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.hideNewMessageModal();
                await this.loadConversations();
                this.openConversation(data.conversation_id);
            } else {
                let errorMsg = data.message || 'Błąd podczas tworzenia konwersacji';
                if (data.error) {
                    console.error('API Error:', data.error);
                    errorMsg = data.message + '\n\nSzczegóły: ' + data.error;
                }
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Błąd:', error);
            alert('Wystąpił błąd połączenia. Sprawdź konsolę przeglądarki.');
        }
    },
    
    // Pomocnicze funkcje
    formatTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diff = Math.floor((now - time) / 1000);
        
        if (diff < 60) return 'teraz';
        if (diff < 3600) return `${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d`;
        
        return time.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' });
    },
    
    formatMessageTime(timestamp) {
        const time = new Date(timestamp);
        return time.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
    },
    
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    },
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    },
    
    // Funkcja globalna do aktualizacji licznika - może być wywołana z innych stron
    async updateGlobalMessagesCount() {
        try {
            const response = await fetch('includes/api/get_unread_messages_count.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateMessagesCount(data.unread_count);
            }
        } catch (error) {
            console.error('Błąd podczas pobierania liczby wiadomości:', error);
        }
    }
};

// Eksportuj
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Messages;
}
