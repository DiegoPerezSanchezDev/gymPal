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
    <!-- El `ref="rootEl"` le da un "nombre" al div para que podamos referenciarlo en el script -->
    <div ref="rootEl" class="relative">
        <!-- Contenedor para los chips seleccionados y el botón '+' -->
        <div class="flex flex-wrap items-center gap-2">
            <span v-for="interest in selectedInterests" :key="interest.id" class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-full">
                {{ interest.name }}
                <button type="button" class="ml-2 -mr-1 flex-shrink-0 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:text-red-500" @click="removeInterest(interest.id)">
                    <svg class="h-3 w-3" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                </button>
            </span>
            <button type="button" @click="showDropdown = !showDropdown" class="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-indigo-200 text-indigo-700 rounded-full transition">
                <span class="text-xl font-bold -mt-0.5">+</span>
            </button>
        </div>
        
        <!-- Dropdown de intereses disponibles -->
        <div v-if="showDropdown" class="absolute z-10 bg-white border border-gray-200 rounded-md shadow-lg mt-2 w-full md:w-64">
            <ul>
                <li v-for="interest in paginatedInterests" :key="interest.id" class="border-b border-gray-100 last:border-b-0">
                    <button type="button" class="w-full text-left px-4 py-2 hover:bg-indigo-50" @click="addInterest(interest)">
                        {{ interest.name }}
                    </button>
                </li>
                <li v-if="paginatedInterests.length === 0" class="px-4 py-2 text-gray-400">No hay más intereses</li>
            </ul>
            <!-- Controles de paginación -->
            <div class="flex justify-between items-center px-2 py-1 border-t border-gray-100 bg-gray-50 rounded-b-md">
                <button type="button" @click="prevPage" :disabled="page === 1" class="text-xs px-2 py-1 rounded disabled:opacity-50 hover:bg-gray-200">Anterior</button>
                <span class="text-xs text-gray-600">Pág {{ page }} / {{ totalPages }}</span>
                <button type="button" @click="nextPage" :disabled="page === totalPages" class="text-xs px-2 py-1 rounded disabled:opacity-50 hover:bg-gray-200">Siguiente</button>
            </div>
        </div>
    </div>
</template>