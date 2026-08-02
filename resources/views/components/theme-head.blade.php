<script>
    (() => {
        try {
            const allowedThemes = ['light', 'dark', 'system'];
            const accountTheme = @json(auth()->user()?->theme_preference);
            const savedSettingsTheme = @json(session('saved_theme_preference'));

            if (allowedThemes.includes(savedSettingsTheme)) {
                localStorage.setItem('theme', savedSettingsTheme);
            }

            const savedTheme = savedSettingsTheme || localStorage.getItem('theme') || accountTheme || 'system';
            const preference = allowedThemes.includes(savedTheme) ? savedTheme : 'system';
            const resolvedTheme = preference === 'system'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : preference;

            document.documentElement.setAttribute('data-theme', resolvedTheme);
            document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
            document.documentElement.setAttribute('data-theme-preference', preference);
        } catch (error) {
            document.documentElement.setAttribute('data-theme', 'light');
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.documentElement.setAttribute('data-theme-preference', 'system');
        }
    })();
</script>
