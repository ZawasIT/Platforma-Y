document.addEventListener('DOMContentLoaded', () => { // eslint-disable-line
    const queryDiv = document.getElementById('search-query');
    const query = queryDiv ? queryDiv.dataset.query.trim() : '';
    const resultsContainer = document.getElementById('search-results');
    const tabs = document.querySelectorAll('.search-tab');

    if (!resultsContainer) {
        console.error('Search results container not found.');
        return;
    }

    if (!query) {
        resultsContainer.innerHTML = '<div class="no-posts">Proszę wpisać frazę do wyszukania w pasku powyżej.</div>';
        return;
    }

    let currentTab = 'popular';

    // Helper do pobierania ścieżki bazowej aplikacji (np. /Platforma-Y/)
    const getBasePath = () => {
        return window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    };

    const fetchAndRender = async (tab) => {
        // Natychmiast wyczyść kontener i pokaż stan ładowania
        resultsContainer.innerHTML = '<div class="loading">Ładowanie...</div>';
        currentTab = tab;

        try {
            let url = '';
            if (tab === 'users') {
                url = `includes/api/search_users.php?q=${encodeURIComponent(query)}`;
            } else { // 'popular' lub 'latest'
                url = `includes/api/search_posts.php?q=${encodeURIComponent(query)}&sort=${tab}`;
            }

            // Rozpocznij pobieranie
            const response = await fetch(url);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Szczegóły błędu serwera:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            // Rozwiązanie race condition: Sprawdź, czy zakładka nie zmieniła się w trakcie ładowania
            if (tab !== currentTab) return;

            renderResults(data, tab);

        } catch (error) {
            console.error('Fetch error:', error);
            resultsContainer.innerHTML = '<div class="error">Wystąpił błąd podczas ładowania wyników. Sprawdź konsolę, aby uzyskać więcej informacji.</div>';
        }
    };

    const renderResults = (data, tab) => {
        resultsContainer.innerHTML = '';

        if (!data || data.length === 0) {
            resultsContainer.innerHTML = `<div class="no-posts">Brak wyników dla "${escapeHTML(query)}".</div>`;
            return;
        }

        // Walidacja danych i renderowanie
        if (tab === 'users') {
            // Sprawdź, czy pierwszy element wygląda jak obiekt użytkownika
            if (typeof data[0].username === 'undefined') {
                console.error('Error: Expected user data for "users" tab, but received something else.', data);
                resultsContainer.innerHTML = '<div class="error">Otrzymano nieprawidłowe dane dla zakładki użytkowników.</div>';
                return;
            }
            const finalHTML = data.map(user => renderUser(user)).join('');
            resultsContainer.innerHTML = finalHTML;

            // Podłącz nasłuchiwanie zdarzeń dla użytkowników (np. przyciski obserwuj)
            if (window.Interactions && typeof window.Interactions.attachEventListeners === 'function') {
                window.Interactions.attachEventListeners(resultsContainer);
            }
        } else { // 'popular' lub 'latest'
            // Sprawdź, czy pierwszy element wygląda jak obiekt posta
            if (typeof data[0].content === 'undefined') {
                console.error('Error: Expected post data for post tabs, but received something else.', data);
                resultsContainer.innerHTML = '<div class="error">Otrzymano nieprawidłowe dane dla zakładki postów.</div>';
                return;
            }
            
            // Użyj globalnej funkcji createPostElement (tej samej co na stronie głównej i profilu)
            if (typeof createPostElement === 'function') {
                data.forEach(post => {
                    const postEl = createPostElement(post);
                    resultsContainer.appendChild(postEl);
                });
            } else {
                console.error('Błąd: Funkcja createPostElement nie jest dostępna.');
                resultsContainer.innerHTML = '<div class="error">Błąd interfejsu: Nie można wygenerować postów.</div>';
                return;
            }
        }
    };

    const renderUser = (user) => {
        const isFollowing = user.is_following == 1;
        const followButtonText = isFollowing ? 'Obserwujesz' : 'Obserwuj';
        const followButtonClass = isFollowing ? 'following' : '';
        const basePath = getBasePath();
        
        return `
            <div class="follow-item" style="padding: 12px 16px; border-bottom: 1px solid var(--bg-border);">
                <a href="${basePath}profile.php?user=${escapeHTML(user.username)}">
                    <img src="${escapeHTML(user.profile_image)}" alt="Avatar" style="width: 48px; height: 48px; border-radius: 50%;">
                </a>
                <div class="follow-info" style="flex: 1; margin-left: 12px;">
                    <a href="${basePath}profile.php?user=${escapeHTML(user.username)}" style="text-decoration: none; color: inherit;">
                        <div class="follow-name">
                            ${escapeHTML(user.full_name)}
                            ${user.verified ? '<svg viewBox="0 0 24 24" class="verified-badge" style="width: 18px; height: 18px; fill: #1D9BF0; display: inline-block; vertical-align: middle; margin-left: 4px;"><g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g></svg>' : ''}
                        </div>
                        <div class="follow-username">@${escapeHTML(user.username)}</div>
                    </a>
                    <p class="profile-bio" style="font-size: 15px; margin-top: 4px;">${escapeHTML(user.bio || '')}</p>
                </div>
                <button class="follow-btn ${followButtonClass}" data-user-id="${user.id}">${followButtonText}</button>
            </div>
        `;
    };

    const escapeHTML = (str) => {
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    };

    // Logika przełączania zakładek
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            if (tab.dataset.tab === currentTab) return;

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            fetchAndRender(tab.dataset.tab);
        });
    });

    // Początkowe pobieranie dla domyślnej zakładki
    fetchAndRender('popular');
});
