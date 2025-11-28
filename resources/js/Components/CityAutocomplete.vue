<script setup>
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

// --- PROPS Y EMITS ---
const props = defineProps({
    modelValue: { type: String, default: '' }, // Para v-model con el nombre de la ciudad
    apiKey: { type: String, required: true }  // La clave de API que le pasaremos
});
const emit = defineEmits(['update:modelValue', 'city-selected']); // 'city-selected' enviará el objeto completo

// --- ESTADO INTERNO DEL COMPONENTE ---
const rootEl = ref(null); // Ref para detectar clics fuera
const searchQuery = ref(props.modelValue); // El texto que el usuario escribe
const suggestions = ref([]);
const isLoading = ref(false);
const isOpen = ref(false);

// --- LÓGICA DE API ---
// Llama a Geoapify, pero solo 300ms después de que el usuario deja de teclear
const fetchSuggestions = debounce(async () => {
    if (searchQuery.value.length < 3) {
        suggestions.value = [];
        isOpen.value = false;
        return;
    }
    isLoading.value = true;
    try {
        const response = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(searchQuery.value)}&type=city&format=json&limit=5&apiKey=${props.apiKey}`);
        const data = await response.json();
        suggestions.value = data.results || [];
        isOpen.value = suggestions.value.length > 0;
    } catch (error) {
        console.error('Error al obtener sugerencias de ciudad:', error);
    } finally {
        isLoading.value = false;
    }
}, 300);

// Cuando el usuario escribe, actualizamos el v-model y buscamos sugerencias
watch(searchQuery, (newValue) => {
    emit('update:modelValue', newValue);
    fetchSuggestions();
});

// Cuando el usuario selecciona una ciudad de la lista
function selectSuggestion(suggestion) {
    const formattedCity = suggestion.city ? `${suggestion.city}, ${suggestion.country}` : suggestion.name;
    searchQuery.value = formattedCity;
    isOpen.value = false;
    // Emitimos un objeto completo con todos los datos al componente padre
    emit('city-selected', {
        name: formattedCity,
        lat: suggestion.lat,
        lon: suggestion.lon
    });
}

// Lógica para cerrar el dropdown si se hace clic fuera
const handleClickOutside = (event) => {
if (rootEl.value && !rootEl.value.contains(event.target)) {
    isOpen.value = false;
}
};
watch(isOpen, (isShown) => {
if (isShown) { document.addEventListener('click', handleClickOutside); } 
else { document.removeEventListener('click', handleClickOutside); }
});
</script>

<template>
    <div ref="rootEl" class="relative">
        <input
            type="text"
            v-model="searchQuery"
            class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-gray-700 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6 transition-colors"
            placeholder="Empieza a escribir una ciudad..."
            autocomplete="off"
        />
        <div v-if="isOpen && (suggestions.length > 0 || isLoading)" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg transition-colors">
            <div v-if="isLoading" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Buscando...</div>
            <ul v-else>
                <li v-for="suggestion in suggestions" :key="suggestion.place_id" class="border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors">
                    <button type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors" @click="selectSuggestion(suggestion)">
                        {{ suggestion.formatted }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>