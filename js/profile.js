// ===== PROFILE.JS =====

document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page - Inicjalizacja...');
    
    // Load user posts
    loadUserPosts();
    
    // Fix image errors
    fixImageErrors();
    
    // Setup banner error handler
    const bannerImg = document.getElementById('bannerImg');
    if (bannerImg) {
        bannerImg.addEventListener('error', function() {
            if (!this.dataset.errorHandled) {
                this.dataset.errorHandled = 'true';
                this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="600" height="200" viewBox="0 0 600 200"%3E%3Crect width="600" height="200" fill="%231DA1F2"/%3E%3Ctext x="300" y="100" text-anchor="middle" dy=".3em" fill="white" font-family="Arial" font-size="24" font-weight="bold"%3EBanner%3C/text%3E%3C/svg%3E';
            }
        });
    }

    // Prevent default for hash links
    preventDefaultHashLinks();

    // Setup tabs
    initProfileTabs();

    // Setup edit buttons
    setupEditButtons();

    // Setup profile follow button (special handling)
    setupProfileFollowButton();
    
    // Setup message button
    setupMessageButton();

    console.log('Profile page - Gotowa!');
});

/**
 * Load user posts
 */
function loadUserPosts(page = 1) {
    const feed = document.querySelector('.feed');
    
    if (!feed) return;
    
    const profileData = getProfileData();
    if (!profileData || !profileData.userId || profileData.userId <= 0) {
        console.error('Nieprawidłowe dane profilu:', profileData);
        feed.innerHTML = '<div class="error">Błąd: Brak danych profilu</div>';
        return;
    }
    
    if (page === 1) {
        feed.innerHTML = '<div class="loading">Ładowanie postów...</div>';
    }
    
    const formData = new FormData();
    formData.append('filter', 'user');
    formData.append('user_id', profileData.userId);
    formData.append('page', page);
    
    fetch('includes/api/get_posts.php', {
        method: 'POST',
        body: formData,
        cache: 'no-store'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (page === 1) {
                feed.innerHTML = '';
            }
            
            if (data.posts.length === 0 && page === 1) {
                const currentProfileData = getProfileData();
                const message = currentProfileData && currentProfileData.isOwnProfile
                    ? 'Nie masz jeszcze postów' 
                    : 'Ten użytkownik nie ma postów';
                feed.innerHTML = `<div class="no-posts">${message}</div>`;
            } else {
                data.posts.forEach(post => {
                    const postElement = createPostElement(post);
                    feed.appendChild(postElement);
                });
                
                if (data.posts.length === 10) {
                    const loadMoreBtn = document.createElement('button');
                    loadMoreBtn.className = 'load-more-btn';
                    loadMoreBtn.textContent = 'Załaduj więcej';
                    loadMoreBtn.onclick = () => {
                        loadMoreBtn.remove();
                        loadUserPosts(page + 1);
                    };
                    feed.appendChild(loadMoreBtn);
                }
            }
        } else {
            if (page === 1) {
                feed.innerHTML = '<div class="error">Wystąpił błąd podczas ładowania postów</div>';
            }
        }
    })
    .catch(error => {
        console.error('Błąd:', error);
        if (page === 1) {
            feed.innerHTML = '<div class="error">Wystąpił błąd podczas ładowania postów</div>';
        }
    });
}

/**
 * Setup profile tabs
 */
function initProfileTabs() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.dataset.tab;

            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            tabContents.forEach(content => content.classList.remove('active'));

            const targetContent = document.getElementById(tabName + '-tab');
            if (targetContent) {
                targetContent.classList.add('active');
                
                if (tabName === 'posts') {
                    loadUserPosts();
                }
            } else {
                showNotification(`Zakładka "${this.textContent}" w przygotowaniu`, 'info');
            }
        });
    });
}

/**
 * Setup profile follow button
 */
function setupProfileFollowButton() {
    const profileFollowBtn = document.querySelector('.profile-actions .follow-btn');
    
    if (!profileFollowBtn) return;
    
    console.log('Profile follow button found:', profileFollowBtn);
    
    profileFollowBtn.addEventListener('click', async function(e) {
        e.stopPropagation();
        
        const profileData = getProfileData();
        const userId = this.getAttribute('data-user-id') || (profileData ? profileData.userId : null);
        
        if (!userId) {
            console.error('Brak ID użytkownika do obserwowania');
            return;
        }
        
        const isFollowing = this.classList.contains('following');
        const hadFollowingClass = this.classList.contains('following');
        
        console.log('Follow button clicked:', { userId, isFollowing, hadFollowingClass });
        
        // Optymistyczna aktualizacja UI
        if (isFollowing) {
            this.textContent = 'Obserwuj';
            this.classList.remove('following');
        } else {
            this.textContent = 'Obserwujesz';
            this.classList.add('following');
        }
        
        try {
            const response = await fetch('includes/follow_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            });
            
            const data = await response.json();
            console.log('Follow response:', data);
            
            if (data.success) {
                if (data.action === 'followed') {
                    this.textContent = 'Obserwujesz';
                    this.classList.add('following');
                } else {
                    this.textContent = 'Obserwuj';
                    this.classList.remove('following');
                }
                
                // Update followers count
                const followersCount = document.querySelector('.profile-stats .stat:last-child .stat-value');
                if (followersCount) {
                    const currentText = followersCount.textContent;
                    let count = parseInt(currentText.replace(/[^\d]/g, '')) || 0;
                    
                    if (data.action === 'followed') {
                        count++;
                    } else {
                        count--;
                    }
                    
                    followersCount.textContent = formatNumber(count);
                }
            } else {
                // Rollback on error
                if (hadFollowingClass) {
                    this.textContent = 'Obserwujesz';
                    this.classList.add('following');
                } else {
                    this.textContent = 'Obserwuj';
                    this.classList.remove('following');
                }
                console.error('Follow error:', data.message);
            }
        } catch (error) {
            console.error('Follow exception:', error);
            // Rollback on error
            if (hadFollowingClass) {
                this.textContent = 'Obserwujesz';
                this.classList.add('following');
            } else {
                this.textContent = 'Obserwuj';
                this.classList.remove('following');
            }
        }
    });

    profileFollowBtn.addEventListener('mouseenter', function() {
        if (this.classList.contains('following')) {
            this.textContent = 'Przestań obserwować';
        }
    });

    profileFollowBtn.addEventListener('mouseleave', function() {
        if (this.textContent === 'Przestań obserwować') {
            this.textContent = 'Obserwujesz';
        }
    });
}

/**
 * Open edit profile modal
 */
function openEditProfileModal() {
    fetchCurrentProfileData().then(userData => {
        createAndShowEditModal(userData);
    });
}

/**
 * Fetch current profile data
 */
async function fetchCurrentProfileData() {
    try {
        const response = await fetch('includes/api/get_current_user.php', {
            method: 'GET',
            cache: 'no-store'
        });
        
        if (!response.ok) {
            throw new Error('Błąd pobierania danych');
        }
        
        const data = await response.json();
        
        if (data.success) {
            return data.user;
        } else {
            throw new Error(data.message || 'Błąd pobierania danych');
        }
    } catch (error) {
        console.error('Error fetching profile data:', error);
        return {
            full_name: document.querySelector('.profile-name').childNodes[0].textContent.trim(),
            bio: document.querySelector('.profile-bio')?.textContent || '',
            location: '',
            website: '',
            profile_image: document.querySelector('.profile-avatar')?.src || '',
            banner_image: document.querySelector('.profile-banner img')?.src || ''
        };
    }
}

/**
 * Create and show edit modal
 */
function createAndShowEditModal(userData) {
    let modal = document.querySelector('.modal.edit-profile-modal');
    
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal edit-profile-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <button class="modal-close">
                        <svg viewBox="0 0 24 24"><g><path d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"></path></g></svg>
                    </button>
                    <h2 class="modal-title">Edytuj profil</h2>
                    <button class="modal-save">Zapisz</button>
                </div>
                <div class="modal-body">
                    <div class="edit-banner">
                        <img src="${userData.banner_image || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22600%22 height=%22200%22 viewBox=%220 0 600 200%22%3E%3Crect width=%22600%22 height=%22200%22 fill=%22%231DA1F2%22/%3E%3C/svg%3E'}" alt="Banner">
                        <div class="edit-banner-overlay">
                            <button class="banner-upload-btn" title="Dodaj zdjęcie">
                                <svg viewBox="0 0 24 24"><g><path d="M9.697 3H11v2h-.697l-3 2H5c-.276 0-.5.224-.5.5v11c0 .276.224.5.5.5h14c.276 0 .5-.224.5-.5V10h2v8.5c0 1.381-1.119 2.5-2.5 2.5H5c-1.381 0-2.5-1.119-2.5-2.5v-11C2.5 6.119 3.619 5 5 5h1.697l3-2zM12 10.5c-1.105 0-2 .895-2 2s.895 2 2 2 2-.895 2-2-.895-2-2-2zm-4 2c0-2.209 1.791-4 4-4s4 1.791 4 4-1.791 4-4 4-4-1.791-4-4zM17 2c0 1.657-1.343 3-3 3v1c1.657 0 3 1.343 3 3h1c0-1.657 1.343-3 3-3V5c-1.657 0-3-1.343-3-3h-1z"></path></g></svg>
                            </button>
                        </div>
                    </div>
                    <div class="edit-avatar-wrapper">
                        <img src="${userData.profile_image}" alt="Avatar" class="edit-avatar">
                        <div class="avatar-upload-overlay">
                            <button class="avatar-upload-btn" title="Dodaj zdjęcie">
                                <svg viewBox="0 0 24 24"><g><path d="M9.697 3H11v2h-.697l-3 2H5c-.276 0-.5.224-.5.5v11c0 .276.224.5.5.5h14c.276 0 .5-.224.5-.5V10h2v8.5c0 1.381-1.119 2.5-2.5 2.5H5c-1.381 0-2.5-1.119-2.5-2.5v-11C2.5 6.119 3.619 5 5 5h1.697l3-2zM12 10.5c-1.105 0-2 .895-2 2s.895 2 2 2 2-.895 2-2-.895-2-2-2zm-4 2c0-2.209 1.791-4 4-4s4 1.791 4 4-1.791 4-4 4-4-1.791-4-4z"></path></g></svg>
                            </button>
                        </div>
                    </div>
                    <form class="edit-form">
                        <div class="form-group">
                            <input type="text" id="editName" value="${escapeHtml(userData.full_name)}" maxlength="50" required>
                            <label for="editName">Imię i nazwisko</label>
                        </div>
                        <div class="form-group">
                            <textarea id="editBio" maxlength="160">${escapeHtml(userData.bio || '')}</textarea>
                            <label for="editBio">Bio</label>
                        </div>
                        <div class="form-group">
                            <input type="text" id="editLocation" value="${escapeHtml(userData.location || '')}" placeholder="np. Warszawa, Polska" maxlength="30">
                            <label for="editLocation">Lokalizacja</label>
                        </div>
                        <div class="form-group">
                            <input type="url" id="editWebsite" value="${escapeHtml(userData.website || '')}" placeholder="https://example.com" maxlength="100">
                            <label for="editWebsite">Strona internetowa</label>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        setupModalCloseHandlers(modal);
        setupModalImageUpload(modal);
        
        const saveBtn = modal.querySelector('.modal-save');
        saveBtn.addEventListener('click', saveProfile);
    } else {
        modal.querySelector('#editName').value = userData.full_name;
        modal.querySelector('#editBio').value = userData.bio || '';
        modal.querySelector('#editLocation').value = userData.location || '';
        modal.querySelector('#editWebsite').value = userData.website || '';
        modal.querySelector('.edit-avatar').src = userData.profile_image;
        modal.querySelector('.edit-banner img').src = userData.banner_image || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22600%22 height=%22200%22 viewBox=%220 0 600 200%22%3E%3Crect width=%22600%22 height=%22200%22 fill=%22%231DA1F2%22/%3E%3C/svg%3E';
        
        // Clear pending uploads when reopening
        if (modal.pendingUploads) {
            delete modal.pendingUploads;
        }
    }
    
    modal.classList.add('active');
}

/**
 * Save profile
 */
async function saveProfile() {
    const name = document.getElementById('editName').value.trim();
    const bio = document.getElementById('editBio').value.trim();
    const location = document.getElementById('editLocation').value.trim();
    const website = document.getElementById('editWebsite').value.trim();
    
    if (!name) {
        showNotification('Imię i nazwisko jest wymagane', 'error');
        return;
    }
    
    if (name.length > 50) {
        showNotification('Imię i nazwisko może mieć maksymalnie 50 znaków', 'error');
        return;
    }
    
    if (bio.length > 160) {
        showNotification('Bio może mieć maksymalnie 160 znaków', 'error');
        return;
    }
    
    if (location.length > 30) {
        showNotification('Lokalizacja może mieć maksymalnie 30 znaków', 'error');
        return;
    }
    
    if (website.length > 100) {
        showNotification('Strona internetowa może mieć maksymalnie 100 znaków', 'error');
        return;
    }
    
    const saveBtn = document.querySelector('.modal-save');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Zapisywanie...';
    
    try {
        const formData = new FormData();
        formData.append('full_name', name);
        formData.append('bio', bio);
        formData.append('location', location);
        formData.append('website', website);
        
        // Add pending image uploads
        const modal = document.querySelector('.modal.edit-profile-modal');
        if (modal && modal.pendingUploads) {
            if (modal.pendingUploads.profile) {
                formData.append('profile_image', modal.pendingUploads.profile);
            }
            if (modal.pendingUploads.banner) {
                formData.append('banner_image', modal.pendingUploads.banner);
            }
        }
        
        const response = await fetch('includes/update_profile.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update displayed data
            const profileNameElement = document.querySelector('.profile-name');
            const nameNode = profileNameElement.childNodes[0];
            if (nameNode.nodeType === Node.TEXT_NODE) {
                nameNode.textContent = name + '\n                    ';
            }
            
            let bioElement = document.querySelector('.profile-bio');
            if (bio) {
                if (!bioElement) {
                    bioElement = document.createElement('p');
                    bioElement.className = 'profile-bio';
                    document.querySelector('.profile-details').insertBefore(
                        bioElement,
                        document.querySelector('.profile-meta')
                    );
                }
                bioElement.textContent = bio;
            } else if (bioElement) {
                bioElement.remove();
            }
            
            // Update profile image if changed
            if (data.user.profile_image) {
                const profileImg = document.getElementById('profileImg');
                if (profileImg) {
                    profileImg.src = data.user.profile_image + '?t=' + Date.now();
                }
                // Update sidebar avatar
                const sidebarAvatar = document.querySelector('.sidebar-left .user-info img');
                if (sidebarAvatar) {
                    sidebarAvatar.src = data.user.profile_image + '?t=' + Date.now();
                }
            }
            
            // Update banner image if changed
            if (data.user.banner_image) {
                const bannerImg = document.querySelector('.profile-banner img');
                if (bannerImg) {
                    bannerImg.src = data.user.banner_image + '?t=' + Date.now();
                } else {
                    // Create img if doesn't exist
                    const banner = document.querySelector('.profile-banner');
                    if (banner) {
                        banner.innerHTML = `<img src="${data.user.banner_image}?t=${Date.now()}" alt="Banner" id="bannerImg">`;
                    }
                }
            }
            
            // Clear pending uploads
            const modal = document.querySelector('.modal.edit-profile-modal');
            if (modal && modal.pendingUploads) {
                delete modal.pendingUploads;
            }
            
            showNotification('Profil został zaktualizowany!', 'success');
            closeModal(modal);
        } else {
            showNotification(data.message || 'Błąd podczas zapisywania', 'error');
        }
    } catch (error) {
        console.error('Error saving profile:', error);
        showNotification('Wystąpił błąd podczas zapisywania', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
}

/**
 * Setup message button
 */
function setupMessageButton() {
    const messageBtn = document.querySelector('.message-btn');
    if (!messageBtn) return;
    
    messageBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const userId = this.dataset.userId;
        if (!userId) {
            console.error('No user ID found');
            return;
        }
        
        // Disable button podczas ładowania
        this.disabled = true;
        const originalHTML = this.innerHTML;
        this.innerHTML = '<svg class="spinning" viewBox="0 0 24 24" width="20" height="20"><g><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" opacity=".3"></path><path d="M12 2C6.48 2 2 6.48 2 12h2c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8v2c5.52 0 10-4.48 10-10S17.52 2 12 2z"></path></g></svg>';
        
        try {
            const formData = new FormData();
            formData.append('user_id', userId);
            
            const response = await fetch('includes/api/create_conversation.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Przekieruj do strony wiadomości z otwartą konwersacją
                window.location.href = `messages.php?conversation=${data.conversation_id}`;
            } else {
                // Przywróć przycisk
                this.disabled = false;
                this.innerHTML = originalHTML;
                
                // Pokaż szczegółowy komunikat błędu
                let errorMsg = data.message || 'Błąd podczas tworzenia konwersacji';
                if (data.error) {
                    console.error('API Error:', data.error);
                    errorMsg += '\nSprawdź konsolę przeglądarki aby uzyskać więcej informacji.';
                }
                showNotification(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error creating conversation:', error);
            this.disabled = false;
            this.innerHTML = originalHTML;
            showNotification('Wystąpił błąd połączenia. Spróbuj ponownie.', 'error');
        }
    });
}

/**
 * Setup image upload buttons in modal
 */
function setupModalImageUpload(modal) {
    // Banner upload
    const bannerUploadBtn = modal.querySelector('.banner-upload-btn');
    if (bannerUploadBtn) {
        bannerUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/jpeg,image/png,image/gif,image/webp';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    uploadImageInModal(file, 'banner', modal);
                }
            };
            input.click();
        });
    }

    // Avatar upload
    const avatarUploadBtn = modal.querySelector('.avatar-upload-btn');
    if (avatarUploadBtn) {
        avatarUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/jpeg,image/png,image/gif,image/webp';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    uploadImageInModal(file, 'profile', modal);
                }
            };
            input.click();
        });
    }
}

/**
 * Upload image in modal (preview only, actual upload on save)
 */
function uploadImageInModal(file, type, modal) {
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showNotification('Plik jest zbyt duży. Maksymalny rozmiar to 5MB.', 'error');
        return;
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Nieprawidłowy format pliku. Dozwolone: JPG, PNG, GIF, WebP.', 'error');
        return;
    }

    // Preview image in modal
    const reader = new FileReader();
    reader.onload = function(e) {
        if (type === 'banner') {
            const bannerImg = modal.querySelector('.edit-banner img');
            if (bannerImg) {
                bannerImg.src = e.target.result;
            }
        } else if (type === 'profile') {
            const avatarImg = modal.querySelector('.edit-avatar');
            if (avatarImg) {
                avatarImg.src = e.target.result;
            }
        }
    };
    reader.readAsDataURL(file);

    // Store file for later upload
    if (!modal.pendingUploads) {
        modal.pendingUploads = {};
    }
    modal.pendingUploads[type] = file;
}

/**
 * Setup all edit buttons
 */
function setupEditButtons() {
    // Setup edit profile button
    const editProfileBtn = document.getElementById('editProfileBtn');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', openEditProfileModal);
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.profile-notification');
    if (existing) {
        existing.remove();
    }

    const notification = document.createElement('div');
    notification.className = `profile-notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}