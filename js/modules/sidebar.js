// ===== SIDEBAR MODULE =====

/**
 * Ładuje ostatnie posty do prawego sidebara
 */
async function loadRecentPosts() {
    const container = document.getElementById('recent-posts-widget');
    if (!container) return;

    try {
        const response = await fetch('includes/api/get_recent_posts.php?limit=3');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const posts = await response.json();

        if (!posts || posts.length === 0) {
            container.innerHTML = '<div style="padding: 12px; color: var(--text-secondary); font-size: 14px;">Brak postów do wyświetlenia</div>';
            return;
        }

        // Renderuj posty w kompaktowej formie
        const postsHTML = posts.map(post => renderCompactPost(post)).join('');
        container.innerHTML = postsHTML;

    } catch (error) {
        console.error('Błąd ładowania ostatnich postów:', error);
        container.innerHTML = '<div style="padding: 12px; color: var(--text-secondary); font-size: 14px;">Błąd ładowania postów</div>';
    }
}

/**
 * Renderuje post w kompaktowej formie dla sidebara
 */
function renderCompactPost(post) {
    const timeAgo = getTimeAgo(post.created_at);
    const content = escapeHTML(post.content);
    const truncatedContent = content.length > 100 ? content.substring(0, 100) + '...' : content;
    
    return `
        <div class="compact-post" style="padding: 12px; border-bottom: 1px solid var(--bg-border); cursor: pointer; transition: background-color 0.2s;" 
             onclick="window.location.href='index.php#post-${post.id}'">
            <div style="display: flex; align-items: start; gap: 8px;">
                <img src="${escapeHTML(post.profile_image)}" 
                     alt="Avatar" 
                     style="width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;">
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                        <span style="font-weight: 700; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${escapeHTML(post.full_name)}
                        </span>
                        ${post.verified ? '<svg viewBox="0 0 24 24" style="width: 14px; height: 14px; fill: #1D9BF0; flex-shrink: 0;"><g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g></svg>' : ''}
                    </div>
                    <div style="font-size: 14px; color: var(--text-primary); line-height: 1.4; word-wrap: break-word;">
                        ${truncatedContent}
                    </div>
                    <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                        ${timeAgo}
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Formatuje datę na "X minut temu", "X godzin temu" itp.
 */
function getTimeAgo(dateString) {
    const now = new Date();
    const postDate = new Date(dateString);
    const diffInSeconds = Math.floor((now - postDate) / 1000);

    if (diffInSeconds < 60) {
        return 'Teraz';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} min`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} godz.`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        if (days === 1) return '1 dzień';
        return `${days} dni`;
    }
}

/**
 * Escape HTML aby zapobiec XSS
 */
function escapeHTML(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Inicjalizacja modułu sidebar
 */
function init() {
    loadRecentPosts();
}

// Export dla użycia w innych modułach
if (typeof window !== 'undefined') {
    window.Sidebar = {
        init,
        loadRecentPosts
    };
}

// Export dla Node.js (jeśli używane)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        init,
        loadRecentPosts
    };
}
