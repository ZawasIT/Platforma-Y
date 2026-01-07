// js/theme.js - Obsługa przełączania trybów jasny/ciemny

const ThemeToggle = {
    init() {
        this.themeToggleBtn = document.getElementById('theme-toggle');
        this.sunIcon = document.querySelector('.theme-icon-sun');
        this.moonIcon = document.querySelector('.theme-icon-moon');
        
        if (!this.themeToggleBtn) return;
        
        // Ustaw początkową ikonę
        this.updateIcon();
        
        // Dodaj event listener
        this.themeToggleBtn.addEventListener('click', () => this.toggleTheme());
    },
    
    toggleTheme() {
        const currentTheme = this.getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        this.setTheme(newTheme);
    },
    
    setTheme(theme) {
        if (theme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'dark');
        }
        
        this.updateIcon();
    },
    
    getCurrentTheme() {
        return document.documentElement.hasAttribute('data-theme') ? 'light' : 'dark';
    },
    
    updateIcon() {
        const currentTheme = this.getCurrentTheme();
        
        if (currentTheme === 'light') {
            // W trybie jasnym pokazuj księżyc (przełącz na ciemny)
            this.sunIcon.style.display = 'none';
            this.moonIcon.style.display = 'block';
        } else {
            // W trybie ciemnym pokazuj słońce (przełącz na jasny)
            this.sunIcon.style.display = 'block';
            this.moonIcon.style.display = 'none';
        }
    }
};

// Inicjalizuj po załadowaniu DOM
document.addEventListener('DOMContentLoaded', () => {
    ThemeToggle.init();
});
