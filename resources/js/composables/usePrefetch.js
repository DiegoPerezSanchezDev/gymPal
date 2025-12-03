import { onMounted } from 'vue';

/**
 * Composable para mejorar el rendimiento con prefetch
 * Nota: Inertia.js maneja el prefetch automáticamente cuando usas el atributo prefetch en Link
 */
export function usePrefetch() {
    /**
     * Retorna los event handlers para prefetch on hover/touch
     * En realidad, solo sirve como indicador visual de que el enlace es importante
     * El prefetch real se hace con el atributo `prefetch` en el componente Link
     * @param {string} url - URL (no se usa, solo para documentación)
     * @returns {Object} - Event handlers vacíos (el prefetch lo hace Inertia)
     */
    const prefetchOnHover = (url) => {
        // Retornamos handlers vacíos porque Inertia maneja el prefetch
        // cuando usamos el atributo prefetch="hover" o prefetch="mount" en Link
        return {};
    };

    return {
        prefetchOnHover,
    };
}

/**
 * Hook para indicar que esta página debería precargar rutas importantes
 * Nota: En Inertia v2, el prefetch se configura directamente en los componentes Link
 */
export function useAutoPrefetch() {
    // En Inertia v2, no necesitamos hacer nada aquí
    // El prefetch se maneja con el atributo prefetch en cada Link
    onMounted(() => {
        // Placeholder para futuras mejoras
    });
}
