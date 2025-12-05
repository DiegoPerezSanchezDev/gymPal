```vue
<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

const props = defineProps({ 
    post: Object,  // Deprecated - mantener por compatibilidad
    item: Object,  // Nuevo - genérico
    itemType: {    // 'post' o 'workout'
        type: String,
        default: 'post'
    }
});
const emit = defineEmits(['close']);

const { success, error } = useToast();

// Determinar qué item usar (compatibilidad con código antiguo)
const currentItem = computed(() => props.item || props.post);
const currentType = computed(() => props.itemType || 'post');

const gymPals = ref([]);
const isLoading = ref(false);
const isSharing = ref(false);
const selectedRecipients = ref([]); // Array para múltiples seleccionados
const searchQuery = ref('');

const loadGymPals = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('connections.gym-pals'));
        gymPals.value = response.data;
    } catch (err) {
        console.error('Error al cargar conexiones:', err);
        error('Error al cargar tus conexiones');
    } finally {
        isLoading.value = false;
    }
};

const filteredGymPals = computed(() => {
    if (!searchQuery.value) return gymPals.value;
    const query = searchQuery.value.toLowerCase();
    return gymPals.value.filter(gymPal => 
        gymPal.name.toLowerCase().includes(query) || 
        gymPal.username.toLowerCase().includes(query)
    );
});

// Función para alternar selección
const toggleRecipient = (gymPal) => {
    const index = selectedRecipients.value.findIndex(r => r.id === gymPal.id);
    if (index === -1) {
        selectedRecipients.value.push(gymPal);
    } else {
        selectedRecipients.value.splice(index, 1);
    }
};

// Verificar si está seleccionado
const isSelected = (gymPal) => {
    return selectedRecipients.value.some(r => r.id === gymPal.id);
};

// Compartir con todos los seleccionados
const sharePost = async () => {
    if (selectedRecipients.value.length === 0) {
        error('Selecciona al menos un GymPal');
        return;
    }

    isSharing.value = true;
    let successCount = 0;
    let errorCount = 0;

    try {
        // Determinar la ruta según el tipo
        const shareRoute = currentType.value === 'workout' 
            ? route('workouts.share', currentItem.value.id)
            : route('posts.share', currentItem.value.id);

        // Enviar una petición por cada destinatario
        for (const recipient of selectedRecipients.value) {
            try {
                await axios.post(shareRoute, {
                    recipient_id: recipient.id
                });
                successCount++;
            } catch (err) {
                console.error(`Error al compartir con ${recipient.name}:`, err);
                errorCount++;
            }
        }

        if (successCount > 0) {
            const itemName = currentType.value === 'workout' ? 'rutina' : 'publicación';
            success(`${itemName.charAt(0).toUpperCase() + itemName.slice(1)} compartida con ${successCount} ${successCount === 1 ? 'persona' : 'personas'}`);
        }
        
        if (errorCount > 0) {
            error(`Error al compartir con ${errorCount} ${errorCount === 1 ? 'persona' : 'personas'}`);
        }

        emit('close');
    } catch (err) {
        console.error('Error al compartir:', err);
        error('Error al compartir');
    } finally {
        isSharing.value = false;
    }
};

onMounted(() => {
    loadGymPals();
});
</script>

<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 dark:bg-opacity-60 z-50 flex items-center justify-center p-4 transition-colors">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] flex flex-col transition-colors">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col gap-1 transition-colors">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white transition-colors">
                        {{ currentType === 'workout' ? 'Compartir Rutina' : 'Compartir Publicación' }}
                    </h2>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl leading-none transition-colors">&times;</button>
                </div>
                <p v-if="currentItem" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                    {{ currentType === 'workout' ? currentItem.name : (currentItem.description || 'Publicación') }}
                </p>
            </div>

            <!-- Search Bar -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 transition-colors">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar GymPals"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none transition-colors"
                />
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="flex-1 flex items-center justify-center p-8">
                <div class="text-gray-500 dark:text-gray-400 transition-colors">Cargando conexiones...</div>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredGymPals.length === 0" class="flex-1 flex items-center justify-center p-8">
                <div class="text-center text-gray-500 dark:text-gray-400 transition-colors">
                    <p class="font-semibold mb-2">{{ searchQuery ? 'No se encontraron resultados' : 'No tienes conexiones aún' }}</p>
                    <p class="text-sm">{{ searchQuery ? 'Intenta con otro nombre' : 'Conéctate con otros GymPals para compartir' }}</p>
                </div>
            </div>

            <!-- GymPals List -->
            <div v-else class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                <div class="space-y-2">
                    <button
                        v-for="gymPal in filteredGymPals"
                        :key="gymPal.id"
                        @click="toggleRecipient(gymPal)"
                        :class="[
                            'w-full flex items-center gap-3 p-3 rounded-lg transition-colors',
                            isSelected(gymPal)
                                ? 'bg-indigo-100 dark:bg-indigo-900/30 border-2 border-indigo-500 dark:border-indigo-500' 
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 border-2 border-transparent'
                        ]"
                    >
                        <img
                            :src="gymPal.profile_picture_url ? `/storage/${gymPal.profile_picture_url}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(gymPal.name)}&background=random`"
                            :alt="gymPal.name"
                            class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                        />
                        <div class="flex-1 text-left">
                            <p class="font-semibold text-gray-800 dark:text-white transition-colors">{{ gymPal.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">@{{ gymPal.username }}</p>
                        </div>
                        <div v-if="isSelected(gymPal)" class="text-indigo-600 dark:text-indigo-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div v-else class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-500"></div>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center gap-3 transition-colors">
                <div></div>
                <div class="flex gap-3">
                    <button 
                        @click="$emit('close')" 
                        class="px-6 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="sharePost" 
                        :disabled="selectedRecipients.length === 0 || isSharing"
                        class="px-6 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ isSharing ? 'Compartiendo...' : 'Compartir' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

