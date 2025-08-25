<script setup>
import { ref, onMounted, watch } from 'vue';
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline'; // O usa tus propios SVGs

// Estado para el tema actual
// Inicializa con el valor guardado en localStorage o el preferido por el sistema
const currentTheme = ref(localStorage.getItem('theme') || 'light');

// Función para aplicar el tema al elemento <html> y guardarlo
const applyTheme = (theme) => {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
    currentTheme.value = theme;
};

// Función para alternar el tema
const toggleTheme = () => {
    const newTheme = currentTheme.value === 'light' ? 'dark' : 'light';
    applyTheme(newTheme);
};

// Aplicar el tema al montar el componente (para la carga inicial de la página)
onMounted(() => {
    // Si no hay nada en localStorage, y el sistema prefiere oscuro, usar oscuro.
    // De lo contrario, el valor de localStorage (o 'light' por defecto) ya se habrá aplicado.
    if (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        applyTheme('dark');
    } else {
        applyTheme(currentTheme.value); // Asegura que se aplique el tema guardado o el inicial
    }
});

// Opcional: Observar cambios en las preferencias del sistema si no hay tema guardado
watch(() => window.matchMedia('(prefers-color-scheme: dark)').matches, (prefersDark) => {
    if (!localStorage.getItem('theme')) { // Solo si el usuario no ha elegido explícitamente
        applyTheme(prefersDark ? 'dark' : 'light');
    }
});

</script>

<template>
    <button
        @click="toggleTheme"
        type="button"
        class="p-2 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        :aria-label="currentTheme === 'light' ? 'Activar modo oscuro' : 'Activar modo claro'"
    >
        <template v-if="currentTheme === 'light'">
            <MoonIcon class="h-6 w-6" />
        </template>
        <template v-else>
            <SunIcon class="h-6 w-6" />
        </template>
    </button>
</template>