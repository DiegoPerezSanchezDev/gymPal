<!-- En resources/js/Components/SharePostModal.vue -->
<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ post: Object });
const emit = defineEmits(['close']);

const gymPals = ref([]);
const isLoading = ref(false);
const isSharing = ref(false);
const selectedRecipient = ref(null);
const searchQuery = ref('');

const loadGymPals = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('connections.gym-pals'));
        gymPals.value = response.data;
    } catch (error) {
        console.error('Error al cargar conexiones:', error);
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

const sharePost = async () => {
    if (!selectedRecipient.value) return;
    
    isSharing.value = true;
    try {
        const response = await axios.post(route('posts.share', { post: props.post.id }), {
            recipient_id: selectedRecipient.value.id
        });
        
        // Mostrar mensaje de éxito y cerrar
        alert('¡Post compartido exitosamente!');
        emit('close');
    } catch (error) {
        console.error('Error al compartir post:', error);
        alert(error.response?.data?.message || 'Error al compartir el post. Inténtalo de nuevo.');
    } finally {
        isSharing.value = false;
    }
};

onMounted(() => {
    loadGymPals();
});
</script>

<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Compartir Publicación</h2>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>

            <!-- Search Bar -->
            <div class="p-4 border-b border-gray-200">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar GymPals..."
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                />
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="flex-1 flex items-center justify-center p-8">
                <div class="text-gray-500">Cargando conexiones...</div>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredGymPals.length === 0" class="flex-1 flex items-center justify-center p-8">
                <div class="text-center text-gray-500">
                    <p class="font-semibold mb-2">{{ searchQuery ? 'No se encontraron resultados' : 'No tienes conexiones aún' }}</p>
                    <p class="text-sm">{{ searchQuery ? 'Intenta con otro nombre' : 'Conéctate con otros GymPals para compartir' }}</p>
                </div>
            </div>

            <!-- GymPals List -->
            <div v-else class="flex-1 overflow-y-auto p-4">
                <div class="space-y-2">
                    <button
                        v-for="gymPal in filteredGymPals"
                        :key="gymPal.id"
                        @click="selectedRecipient = gymPal"
                        :class="[
                            'w-full flex items-center gap-3 p-3 rounded-lg transition-colors',
                            selectedRecipient?.id === gymPal.id 
                                ? 'bg-indigo-100 border-2 border-indigo-500' 
                                : 'hover:bg-gray-100 border-2 border-transparent'
                        ]"
                    >
                        <img
                            :src="gymPal.profile_picture_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(gymPal.name)}&background=random`"
                            :alt="gymPal.name"
                            class="w-12 h-12 rounded-full object-cover"
                        />
                        <div class="flex-1 text-left">
                            <p class="font-semibold text-gray-800">{{ gymPal.name }}</p>
                            <p class="text-sm text-gray-500">@{{ gymPal.username }}</p>
                        </div>
                        <div v-if="selectedRecipient?.id === gymPal.id" class="text-indigo-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 flex justify-end gap-3">
                <button 
                    @click="$emit('close')" 
                    class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 font-semibold transition-colors"
                >
                    Cancelar
                </button>
                <button 
                    @click="sharePost" 
                    :disabled="!selectedRecipient || isSharing"
                    class="px-6 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    {{ isSharing ? 'Compartiendo...' : 'Compartir' }}
                </button>
            </div>
        </div>
    </div>
</template>

