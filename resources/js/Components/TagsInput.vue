<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';

// --- PROPS Y EMITS ---
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  interests: { type: Array, required: true },
});
const emit = defineEmits(['update:modelValue']);

// --- ESTADO INTERNO ---
const rootEl = ref(null); // Ref para el elemento raíz del componente
const showDropdown = ref(false);
const page = ref(1);
const perPage = 5;

// --- COMPUTED PROPERTIES (Lógica de datos) ---
const selectedInterests = computed(() => props.interests.filter(i => props.modelValue.includes(i.id)));
const availableInterests = computed(() => props.interests.filter(i => !props.modelValue.includes(i.id)));
const totalPages = computed(() => Math.max(1, Math.ceil(availableInterests.value.length / perPage)));
const paginatedInterests = computed(() => {
  const start = (page.value - 1) * perPage;
  return availableInterests.value.slice(start, start + perPage);
});

// --- FUNCIONES ---
function addInterest(interest) {
  emit('update:modelValue', [...props.modelValue, interest.id]);
  showDropdown.value = false;
}
function removeInterest(id) {
  emit('update:modelValue', props.modelValue.filter(i => i !== id));
}
function prevPage() { if (page.value > 1) page.value--; }
function nextPage() { if (page.value < totalPages.value) page.value++; }

// --- MANEJO DEL "CLICK AFUERA" ---
const handleClickOutside = (event) => {
  // Si el elemento en el que se hizo clic no está dentro de nuestro componente, cerramos el dropdown.
  if (rootEl.value && !rootEl.value.contains(event.target)) {
    showDropdown.value = false;
  }
};

// Observamos 'showDropdown' para añadir o quitar el listener global.
watch(showDropdown, (isShown) => {
  if (isShown) {
    document.addEventListener('click', handleClickOutside);
    page.value = 1; // Reseteamos la página cada vez que se abre
  } else {
    document.removeEventListener('click', handleClickOutside);
  }
});

// Aseguramos la limpieza del listener si el componente se destruye.
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="rootEl" class="relative">
        <!-- Contenedor para los chips seleccionados -->
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <span v-for="interest in selectedInterests" :key="interest.id" class="inline-flex items-center px-3 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-bold rounded-full border border-indigo-200 dark:border-indigo-800 transition-all animate-bounce-in">
                {{ interest.name }}
                <button type="button" class="ml-2 -mr-1 flex-shrink-0 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:text-red-500 hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors" @click="removeInterest(interest.id)">
                    <svg class="h-3 w-3" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                </button>
            </span>
             <span v-if="selectedInterests.length === 0" class="text-sm text-gray-400 italic">Selecciona tus deportes...</span>
        </div>
        
        <!-- Lista de intereses disponibles (Scroll Horizontal) -->
        <div class="relative">
            <div class="flex overflow-x-auto gap-2 pb-2 scrollbar-hide mask-fade-sides">
                <button 
                    v-for="interest in availableInterests" 
                    :key="interest.id" 
                    type="button" 
                    class="whitespace-nowrap px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm font-medium border border-gray-200 dark:border-gray-700 transition-all active:scale-95 flex-shrink-0"
                    @click="addInterest(interest)"
                >
                    + {{ interest.name }}
                </button>
                <div v-if="availableInterests.length === 0" class="text-sm text-gray-400 italic px-2">
                    ¡Has seleccionado todos!
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.animate-bounce-in {
    animation: bounceIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>