import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    // Inicializar el tema desde localStorage o preferencia del sistema
    const initTheme = () => {
        const savedTheme = localStorage.getItem('theme');

        if (savedTheme) {
            isDark.value = savedTheme === 'dark';
        } else {
            // Detectar preferencia del sistema
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        applyTheme();
    };

    // Aplicar el tema al documento
    const applyTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    // Toggle entre light y dark
    const toggleTheme = () => {
        isDark.value = !isDark.value;
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
        applyTheme();
    };

    // Establecer tema específico
    const setTheme = (theme) => {
        isDark.value = theme === 'dark';
        localStorage.setItem('theme', theme);
        applyTheme();
    };

    // Watch para cambios reactivos
    watch(isDark, () => {
        applyTheme();
    });

    return {
        isDark,
        toggleTheme,
        setTheme,
        initTheme
    };
}
