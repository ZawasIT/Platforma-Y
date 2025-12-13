<?php
// includes/right_sidebar.php

// This file can optionally use $suggestedUsers, if available.
?>
<aside class="sidebar-right">
    <!-- Wyszukiwarka -->
    <form action="search.php" method="GET" class="search-box">
        <svg viewBox="0 0 24 24" class="search-icon" aria-hidden="true">
            <g>
                <path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path>
            </g>
        </svg>
        <input type="text" name="q" placeholder="Szukaj" class="search-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    </form>

    <!-- Co się dzieje? -->
    <div class="widget">
        <h2>Co się dzieje?</h2>
        <div class="trending-item">
            <div class="trending-category">Technologia · Na czasie</div>
            <div class="trending-topic">#ReactJS</div>
            <div class="trending-posts">15,2 tys. postów</div>
        </div>
        <div class="trending-item">
            <div class="trending-category">Polska · Trendy</div>
            <div class="trending-topic">#PlatformaY</div>
            <div class="trending-posts">8,4 tys. postów</div>
        </div>
        <div class="trending-item">
            <div class="trending-category">Programowanie · Na czasie</div>
            <div class="trending-topic">#100DaysOfCode</div>
            <div class="trending-posts">24,5 tys. postów</div>
        </div>
        <div class="trending-item">
            <div class="trending-category">Wiadomości · LIVE</div>
            <div class="trending-topic">Konferencja IT 2025</div>
            <div class="trending-posts">3,2 tys. postów</div>
        </div>
        <a href="#" class="show-more">Pokaż więcej</a>
    </div>

    <!-- Kogo obserwować -->
    <?php if (isset($suggestedUsers) && !empty($suggestedUsers)): ?>
    <div class="widget">
        <h2>Kogo obserwować</h2>
        <?php foreach ($suggestedUsers as $user): ?>
        <div class="follow-item">
            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Avatar">
            <div class="follow-info">
                <div class="follow-name">
                    <?php echo htmlspecialchars($user['full_name']); ?>
                    <?php if ($user['verified']): ?>
                        <svg viewBox="0 0 24 24" class="verified-badge" style="width: 16px; height: 16px; fill: #1D9BF0; display: inline-block; vertical-align: middle; margin-left: 2px;">
                            <g><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2l-4.3-4.29 1.42-1.42 2.88 2.88 6.79-6.79 1.42 1.42-8.21 8.2z"></path></g>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="follow-username">@<?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <button class="follow-btn" data-user-id="<?php echo $user['id']; ?>">Obserwuj</button>
        </div>
        <?php endforeach; ?>
        <a href="#" class="show-more">Pokaż więcej</a>
    </div>
    <?php endif; ?>

    <!-- Stopka -->
    <div class="footer-links">
        <a href="#">Warunki korzystania z usługi</a>
        <a href="#">Zasady prywatności</a>
        <a href="#">Zasady dot. plików cookie</a>
        <a href="#">Ułatwienia dostępu</a>
        <a href="#">Informacje o reklamach</a>
        <a href="#">Więcej ···</a>
        <div class="copyright">© 2025 Platforma Y.</div>
    </div>
</aside>
