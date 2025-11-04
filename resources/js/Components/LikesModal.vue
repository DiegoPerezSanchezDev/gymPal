<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const emits = defineEmits(['close']);

const props = defineProps({
    postId: {
        type: [Number, String],
        required: true,
    }
});

const users = ref([]);
const loading = ref(true);
const loadingMore = ref(false); // CAMBIO: Estado de carga específico para el botón
const page = ref(1);
const hasMorePages = ref(true);
const error = ref(null); // CAMBIO: Estado para manejar errores

async function fetchLikers(p = 1) {
    // CAMBIO: Si es una carga inicial, reseteamos el error
    if (p === 1) {
        error.value = null;
        loading.value = true;
    } else {
        loadingMore.value = true;
    }

    try {
        const urlDestino = route('posts.likes.index', { post: props.postId });
        const response = await axios.get(urlDestino, { params: { page: p } });
        const data = response.data;

        if (data && data.data) {
            users.value = p > 1 ? [...users.value, ...data.data] : data.data;
            hasMorePages.value = !!data.next_page_url;
        } else {
            hasMorePages.value = false;
        }
    } catch(e) {
        console.error("Error en fetchLikers:", e);
        error.value = "No se pudo cargar la lista. Por favor, inténtalo de nuevo."; // CAMBIO: Guardamos el mensaje de error
        hasMorePages.value = false;
    } finally {
        loading.value = false;
        loadingMore.value = false; // CAMBIO: Reseteamos ambos loaders
    }
}

function loadMore() {
    if (hasMorePages.value && !loadingMore.value) {
        page.value++;
        fetchLikers(page.value);
    }
}

onMounted(() => {
    fetchLikers();
});
</script>
<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative animate-fadeInUp">
            <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 text-2xl leading-5">&times;</button>
            
            <!-- CAMBIO: Título con borde inferior para consistencia -->
            <h2 class="text-xl font-bold mb-4 pb-3 text-gray-800 border-b border-gray-200">Les gusta a</h2>
            
            <!-- CAMBIO: Skeleton Loader mejorado -->
            <div v-if="loading" class="space-y-3 mb-4">
                <div v-for="n in 4" :key="n" class="flex items-center gap-3 animate-pulse">
                    <div class="w-9 h-9 bg-gray-200 rounded-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>

            <!-- CAMBIO: Estado de error -->
            <div v-if="error" class="text-center p-8">
                <p class="text-red-600 mb-4">{{ error }}</p>
                <button @click="fetchLikers(1)" class="px-4 py-2 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-semibold transition-colors">Reintentar</button>
            </div>
            
            <ul v-else-if="users.length > 0" class="space-y-1 mb-2 max-h-80 overflow-y-auto custom-scrollbar">
                <!-- CAMBIO: Estilos de item con hover y borde -->
                <li v-for="user in users" :key="user.id" class="flex items-center gap-3 p-2 rounded-lg transition-colors hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                    <img :src="user.profile_picture_url || ('https://ui-avatars.com/api/?name=' + user.name + '&background=random')" alt="avatar" class="w-9 h-9 rounded-full object-cover" />
                    <div>
                        <Link :href="route('profile.show.public', { user: user.username })" class="font-semibold text-gray-800 hover:underline">
                            {{ user.name }}
                        </Link>
                        <p class="text-sm text-gray-400">@{{ user.username }}</p> <!-- CAMBIO: Username en una nueva línea para limpiar la UI -->
                    </div>
                </li>
            </ul>
            
            <!-- CAMBIO: Botón "Cargar más" con estado de carga -->
            <button v-if="hasMorePages && !loading" @click="loadMore" :disabled="loadingMore" class="mt-3 w-full px-4 py-2 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-semibold transition-colors disabled:opacity-50 disabled:cursor-wait flex items-center justify-center">
                <svg v-if="loadingMore" class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ loadingMore ? 'Cargando...' : 'Cargar más' }}</span>
            </button>
            
            <div v-if="!loading && !error && users.length === 0" class="text-gray-500 text-center p-8">Aún no hay likes en esta publicación.</div>
        </div>
    </div>
</template>

<style scoped>
.animate-fadeInUp {
    animation: fadeInUp .22s cubic-bezier(0.35,1.15,0.5,1.04);
}
@keyframes fadeInUp {
    from { opacity:0; transform: translateY(24px); }
    to { opacity:1; transform: translateY(0); }
}

/* CAMBIO: Scrollbar personalizado */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9; /* gray-100 */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db; /* gray-300 */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af; /* gray-400 */
}
</style>
