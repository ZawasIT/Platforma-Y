// ===== MAIN.JS - Entry Point =====
// Import all modules (note: browser doesn't support ES6 modules by default, so we rely on script order)

document.addEventListener('DOMContentLoaded', function() {
    console.log('Platforma Y - Inicjalizacja...');
    
    // Fix image errors
    fixImageErrors();
    
    // Prevent default for hash links
    preventDefaultHashLinks();
    
    // Initialize all modules
    initFeedTabs();
    initNewPostForm();
    initSearchLink();
    initInteractionHandlers();
    initPostMenuHandlers();
    initReplyHandlers();
    
    // Initialize notifications module
    if (typeof Notifications !== 'undefined') {
        Notifications.init();
    }
    
    // Initialize media module (emoji, images)
    if (typeof Media !== 'undefined') {
        Media.init();
    }
    
    // Initialize sidebar module (recent posts, suggested users)
    if (typeof Sidebar !== 'undefined') {
        Sidebar.init();
    }
    
    // Initialize and update messages count on all pages
    if (typeof Messages !== 'undefined') {
        Messages.updateGlobalMessagesCount();
        // Odświeżaj licznik co 30 sekund
        setInterval(() => {
            Messages.updateGlobalMessagesCount();
        }, 30000);
    }
    
    // Load posts only on main page (not profile or search)
    const isProfilePage = !!document.getElementById('profile-data');
    const isSearchPage = !!document.getElementById('search-query') || window.location.pathname.includes('search.php');
    if (!isProfilePage && !isSearchPage) {
        loadPosts();
    }
    
    console.log('Platforma Y - Gotowa!');
});

/**
 * Initialize feed tabs (Dla Ciebie / Obserwowani)
 */
function initFeedTabs() {
    // Nie inicjalizuj zakładek feedu na stronie wyszukiwania (obsługiwane przez search.js)
    if (document.getElementById('search-query') || window.location.pathname.includes('search.php')) return;

    const feedTabs = document.querySelectorAll('.feed-tab:not(.search-tab)');
    if (!feedTabs.length) return;
    
    feedTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            feedTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const tabName = this.dataset.tab;
            if (tabName === 'following') {
                loadPosts(1, 'following');
            } else {
                loadPosts(1, 'for-you');
            }
        });
    });
}

/**
 * Initialize new post form
 */
function initNewPostForm() {
    const textarea = document.querySelector('.post-form textarea');
    const postSubmitBtn = document.querySelector('.post-submit-btn');
    const mainPostButton = document.querySelector('.post-button');

    // Main post button w sidebarze - zawsze inicjalizuj
    if (mainPostButton) {
        mainPostButton.addEventListener('click', function() {
            // Sprawdź czy jesteśmy na stronie głównej
            const isMainPage = window.location.pathname.endsWith('index.php') || 
                               window.location.pathname.endsWith('/') ||
                               window.location.pathname.includes('Platforma_Y/index.php') ||
                               window.location.pathname === '/Platforma_Y/';
            
            if (isMainPage && textarea) {
                // Jesteśmy na stronie głównej - przejdź do textarea
                textarea.focus();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Jesteśmy na innej stronie - przenieś na główną
                window.location.href = 'index.php';
            }
        });
    }

    // Jeśli nie ma formularza, zakończ (ale przycisk sidebar już działa)
    if (!textarea || !postSubmitBtn) return;

    // Funkcja sprawdzająca czy post można wysłać (dostępna globalnie)
    window.updatePostSubmitButton = function() {
        const hasText = textarea.value.trim().length > 0;
        const hasImage = typeof Media !== 'undefined' && Media.getSelectedImage() !== null;
        
        if (hasText || hasImage) {
            postSubmitBtn.style.opacity = '1';
            postSubmitBtn.disabled = false;
        } else {
            postSubmitBtn.style.opacity = '0.5';
            postSubmitBtn.disabled = true;
        }
    };

    // Auto-resize textarea
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
        window.updatePostSubmitButton();
    });

    // Initialize button state
    postSubmitBtn.style.opacity = '0.5';
    postSubmitBtn.disabled = true;

    // Handle post submission
    postSubmitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const content = textarea.value.trim();
        const hasImage = typeof Media !== 'undefined' && Media.getSelectedImage() !== null;
        
        if (content.length === 0 && !hasImage) {
            showNotification('Post nie może być pusty', 'error');
            return;
        }
        
        if (content.length > 280) {
            showNotification('Post może mieć maksymalnie 280 znaków', 'error');
            return;
        }
        
        const originalText = postSubmitBtn.textContent;
        postSubmitBtn.disabled = true;
        postSubmitBtn.textContent = 'Publikowanie...';
        
        const formData = new FormData();
        formData.append('content', content);
        
        // Dodaj zdjęcie jeśli zostało wybrane
        if (typeof Media !== 'undefined' && Media.getSelectedImage()) {
            formData.append('image', Media.getSelectedImage());
        }
        
        fetch('includes/create_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                textarea.value = '';
                textarea.style.height = 'auto';
                
                // Wyczyść wybrane zdjęcie
                if (typeof Media !== 'undefined') {
                    Media.clearSelectedImage();
                }
                
                addPostToFeed(data.post);
                
                showNotification('Post został opublikowany!', 'success');
            } else {
                showNotification(data.message || 'Błąd podczas publikowania', 'error');
            }
            
            postSubmitBtn.disabled = false;
            postSubmitBtn.textContent = originalText;
            postSubmitBtn.style.opacity = '0.5';
        })
        .catch(error => {
            console.error('Błąd:', error);
            showNotification('Wystąpił błąd połączenia', 'error');
            postSubmitBtn.disabled = false;
            postSubmitBtn.textContent = originalText;
        });
    });

    // Character counter
    const charCounter = document.createElement('div');
    charCounter.className = 'char-counter';
    charCounter.style.cssText = 'color: var(--text-secondary); font-size: 13px; margin-right: 12px; margin-left: auto;';
    
    const postActions = document.querySelector('.post-actions');
    if (postActions) {
        postActions.insertBefore(charCounter, postSubmitBtn);
    }

    textarea.addEventListener('input', function() {
        const count = this.value.length;
        charCounter.textContent = count > 0 ? `${count}/280` : '';
        
        if (count > 280) {
            charCounter.style.color = 'var(--accent-red)';
            postSubmitBtn.disabled = true;
            postSubmitBtn.style.opacity = '0.5';
        } else if (count > 260) {
            charCounter.style.color = '#FFD400';
        } else {
            charCounter.style.color = 'var(--text-secondary)';
        }
    });
}

/**
 * Initialize search link
 */
function initSearchLink() {
    const searchLink = document.getElementById('search-link');
    
    if (searchLink) {
        searchLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Sprawdź czy jesteśmy na stronie wyszukiwania
            const isSearchPage = !!document.getElementById('search-query') || 
                                  window.location.pathname.includes('search.php');
            
            if (isSearchPage) {
                // Jesteśmy na stronie wyszukiwania - przejdź do input
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    searchInput.focus();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } else {
                // Jesteśmy na innej stronie - przenieś na wyszukiwanie
                window.location.href = 'search.php';
            }
        });
    }
}