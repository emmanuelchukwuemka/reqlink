// Theme initialization (Run immediately to prevent FOUC)
if (localStorage.getItem('theme') === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
    document.documentElement.classList.add('light-mode');
}

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    if (themeToggle && themeIcon) {
        // Set initial icon state
        const isLightMode = document.documentElement.classList.contains('light-mode');
        if (isLightMode && typeof lucide !== 'undefined') {
            themeIcon.setAttribute('data-lucide', 'moon');
            lucide.createIcons();
        }

        // Toggle click handler
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('light-mode');
            const isLight = document.documentElement.classList.contains('light-mode');
            
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            
            if (typeof lucide !== 'undefined') {
                themeIcon.setAttribute('data-lucide', isLight ? 'moon' : 'sun');
                lucide.createIcons();
            }

            // Dispatch event for other scripts (like maps) to hook into
            document.dispatchEvent(new CustomEvent('themeChanged', { detail: { isLight } }));
        });
    }
});
