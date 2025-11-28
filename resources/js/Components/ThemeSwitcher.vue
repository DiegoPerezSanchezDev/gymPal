<script setup>
import { useDarkMode } from '@/composables/useDarkMode';
import { onMounted } from 'vue';

const { isDark, toggleTheme, initTheme } = useDarkMode();

onMounted(() => {
    initTheme();
});
</script>

<template>
    <button 
        @click="toggleTheme"
        class="p-2 rounded-lg transition-all duration-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300"
        :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    >
        <!-- Sol (Light Mode) -->
        <svg 
            v-if="!isDark"
            class="w-5 h-5 transition-transform duration-300 hover:rotate-45" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
        >
            <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" 
            />
        </svg>
        
        <!-- Luna (Dark Mode) -->
        <svg 
            v-else
            class="w-5 h-5 transition-transform duration-300 hover:rotate-12" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
        >
            <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" 
            />
        </svg>
    </button>
</template>