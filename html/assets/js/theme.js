/**
 * Theme Switcher Module
 * Handles light/dark theme switching across the application
 */

(function() {
    'use strict';

    /**
     * Apply theme to document
     * @param {string} theme - Theme to apply ('light' or 'dark')
     */
    function applyTheme(theme) {
        if (['light', 'dark'].includes(theme)) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            sessionStorage.setItem('selectedTheme', theme);
        }
    }

    /**
     * Get the appropriate theme
     * Priority: sessionStorage > HTML element attribute > 'light' (default)
     * @returns {string} The theme to apply
     */
    function getTheme() {
        // Check sessionStorage first (user changed theme in current session)
        const sessionTheme = sessionStorage.getItem('selectedTheme');
        if (sessionTheme && ['light', 'dark'].includes(sessionTheme)) {
            return sessionTheme;
        }

        // Check current HTML element theme
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        if (currentTheme && ['light', 'dark'].includes(currentTheme)) {
            return currentTheme;
        }

        // Default to light
        return 'light';
    }

    /**
     * Initialize theme on page load
     */
    function initializeTheme() {
        const theme = getTheme();
        applyTheme(theme);
    }

    // Apply theme immediately (before page render completes)
    initializeTheme();

    // Re-apply if needed when DOM is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTheme);
    }

    // Export for use in other scripts
    window.ThemeSwitcher = {
        applyTheme: applyTheme,
        getTheme: getTheme
    };
})();
