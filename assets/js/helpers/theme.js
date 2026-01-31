const getPreferredTheme = () => {
    const storedTheme = localStorage.getItem('color-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
        return 'dark';
    }
    return 'light';
};

const applyTheme = (theme) => {
    const isDark = theme === 'dark';
    document.documentElement.classList.toggle('dark', isDark);
    localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
};

const initThemeToggle = ({
    buttonId = 'theme-toggle',
    darkIconId = 'theme-toggle-dark-icon',
    lightIconId = 'theme-toggle-light-icon',
} = {}) => {
    const toggleButton = document.getElementById(buttonId);
    const darkIcon = document.getElementById(darkIconId);
    const lightIcon = document.getElementById(lightIconId);

    if (!toggleButton || !darkIcon || !lightIcon) {
        return;
    }

    const initialTheme = getPreferredTheme();
    applyTheme(initialTheme);
    darkIcon.classList.toggle('hidden', initialTheme === 'dark');
    lightIcon.classList.toggle('hidden', initialTheme !== 'dark');

    toggleButton.addEventListener('click', () => {
        const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
        applyTheme(nextTheme);
        darkIcon.classList.toggle('hidden', nextTheme === 'dark');
        lightIcon.classList.toggle('hidden', nextTheme !== 'dark');
    });
};

export { applyTheme, getPreferredTheme, initThemeToggle };
