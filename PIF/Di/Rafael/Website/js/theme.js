class ThemeManager {
    constructor() {
        this.themeToggle = document.getElementById('sidebar-theme-toggle');
        this.htmlElement = document.documentElement;
        this.init();
    }

    init() {
        // Check for saved theme or system preference
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Set initial theme
        if (savedTheme) {
            this.setTheme(savedTheme);
        } else if (systemPrefersDark) {
            this.setTheme('dark');
        } else {
            this.setTheme('light');
        }

        // Add click event listener
        this.themeToggle?.addEventListener('click', () => this.toggleTheme());

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    setTheme(theme) {
        if (theme === 'dark') {
            this.htmlElement.classList.add('dark-mode');
            this.htmlElement.classList.remove('light-mode');
            localStorage.setItem('theme', 'dark');
            
            // Update button text
            if (this.themeToggle) {
                const icon = this.themeToggle.querySelector('i');
                const text = this.themeToggle.querySelector('.theme-text');
                if (icon) {
                    icon.classList.remove('bi-moon-fill');
                    icon.classList.add('bi-sun-fill');
                }
                if (text) {
                    text.textContent = 'Light Mode';
                }
            }
        } else {
            this.htmlElement.classList.remove('dark-mode');
            this.htmlElement.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
            
            // Update button text
            if (this.themeToggle) {
                const icon = this.themeToggle.querySelector('i');
                const text = this.themeToggle.querySelector('.theme-text');
                if (icon) {
                    icon.classList.remove('bi-sun-fill');
                    icon.classList.add('bi-moon-fill');
                }
                if (text) {
                    text.textContent = 'Dark Mode';
                }
            }
        }
    }

    toggleTheme() {
        const isDark = this.htmlElement.classList.contains('dark-mode');
        this.setTheme(isDark ? 'light' : 'dark');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});