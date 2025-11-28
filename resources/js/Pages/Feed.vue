<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import PostSkeleton from '@/Components/Skeletons/PostSkeleton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import DeletePostModal from '@/Components/DeletePostModal.vue';

const props = defineProps({
    posts: Object,
    title: String,
    activeTab: String, 
});

// Variable reactiva para almacenar la lista de todos los posts
const allPosts = ref(props.posts.data);
const postToDelete = ref(null);
const isLoading = ref(false);
const isLoadingMore = ref(false);

// Variable para controlar la URL de la siguiente página
let nextPageUrl = ref(props.posts.next_page_url);

// Detectar navegación inicial vs actualizaciones
onMounted(() => {
    // Si no hay posts y no es la primera carga (ej: navegación SPA), podría ser loading
    // Pero Inertia maneja el estado inicial. Lo usaremos para cambios de tab.
});

watch(() => props.posts, (newPosts) => {
    if (newPosts.current_page === 1) {
        allPosts.value = newPosts.data;
    }
    nextPageUrl.value = newPosts.next_page_url;
    isLoading.value = false;
});

function setActiveTab(tabName) {
    isLoading.value = true;
    router.get(route('feed.index'), { tab: tabName }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            allPosts.value = page.props.posts.data;
            nextPageUrl.value = page.props.posts.next_page_url;
            isLoading.value = false;
        },
        onFinish: () => isLoading.value = false
    });
}

function loadMorePosts() {
    if (!nextPageUrl.value) return;

    isLoadingMore.value = true;
    router.get(nextPageUrl.value, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            allPosts.value = [...allPosts.value, ...page.props.posts.data];
            nextPageUrl.value = page.props.posts.next_page_url;
            isLoadingMore.value = false;
        },
        onFinish: () => isLoadingMore.value = false
    });
}

function openDeleteModal(post) {
    postToDelete.value = post;
}

function closeDeleteModal() {
    postToDelete.value = null;
}

</script>

<template>
    <Head :title="title || 'Feed'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                        Feed
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Descubre contenido</p>
                </div>
            </div>
        </template>

        <!-- Sección de Pestañas (Tabs) - Mejorada -->
        <div class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-100 dark:border-gray-700 sticky top-16 z-30 transition-colors">
            <div class="max-w-7xl mx-auto px-4">
                <nav class="flex gap-1" aria-label="Tabs">
                    <button @click="setActiveTab('siguiendo')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative',
                                props.activeTab === 'siguiendo' 
                                    ? 'text-indigo-600 dark:text-indigo-400 bg-gradient-to-b from-indigo-50 dark:from-indigo-900/30 to-transparent' 
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Siguiendo
                        <span v-if="props.activeTab === 'siguiendo'" class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-full"></span>
                    </button>
                    <button @click="setActiveTab('populares')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative',
                                props.activeTab === 'populares' 
                                    ? 'text-indigo-600 dark:text-indigo-400 bg-gradient-to-b from-indigo-50 dark:from-indigo-900/30 to-transparent' 
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" /></svg>
                        Populares
                        <span v-if="props.activeTab === 'populares'" class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-full"></span>
                    </button>
                    <button @click="setActiveTab('cerca')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative',
                                props.activeTab === 'cerca' 
                                    ? 'text-indigo-600 dark:text-indigo-400 bg-gradient-to-b from-indigo-50 dark:from-indigo-900/30 to-transparent' 
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Cerca
                        <span v-if="props.activeTab === 'cerca'" class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-full"></span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Contenido del Feed -->
        <div class="container mx-auto px-2 sm:px-4 py-4 md:py-8">
            <h2 v-if="title" class="text-2xl font-bold text-gray-800 dark:text-white mb-6 hidden md:block text-center transition-colors">{{ title }}</h2>

            <!-- Estado de Carga (Skeletons) -->
            <div v-if="isLoading" class="space-y-6 max-w-2xl mx-auto">
                <PostSkeleton v-for="n in 3" :key="n" />
            </div>

            <!-- Lista de Posts -->
            <div v-else-if="allPosts.length > 0">
                <div class="space-y-6">
                    <PostCard v-for="post in allPosts" :key="post.id" :post="post"
                            @delete-post="openDeleteModal(post)"
                    />
                </div>

                <!-- Botón para Cargar Más -->
                <div v-if="nextPageUrl" class="text-center mt-8 mb-12">
                    <button 
                        @click="loadMorePosts" 
                        :disabled="isLoadingMore"
                        class="bg-indigo-600 text-white font-semibold py-2 px-6 rounded-full hover:bg-indigo-700 transition-all transform hover:scale-105 disabled:opacity-50 disabled:hover:scale-100 shadow-md"
                    >
                        <span v-if="isLoadingMore" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cargando...
                        </span>
                        <span v-else>Cargar más</span>
                    </button>
                </div>
            </div>
        
            <!-- Estado Vacío -->
            <div v-else class="text-center py-12 max-w-lg mx-auto bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 p-8 transition-colors">
                <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl transition-colors">
                    {{ activeTab === 'populares' ? '🔥' : (activeTab === 'cerca' ? '📍' : '📭') }}
                </div>
                
                <h3 class="mt-2 text-xl font-bold text-gray-900 dark:text-white transition-colors">
                    {{ 
                        activeTab === 'populares' ? 'Aún no hay tendencias' : 
                        (activeTab === 'cerca' ? 'No hay actividad cercana' : 'Tu feed está tranquilo') 
                    }}
                </h3>
                
                <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-sm mx-auto transition-colors">
                    {{ 
                        activeTab === 'populares' ? 'Las publicaciones más destacadas de la comunidad aparecerán aquí. ¡Crea contenido genial para ser el primero!' : 
                        (activeTab === 'cerca' ? 'Parece que no hay GymPals activos cerca de tu ubicación por ahora.' : 'Sigue a más atletas o crea tu primera publicación para empezar.') 
                    }}
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link :href="route('posts.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent shadow-lg text-sm font-bold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transform hover:-translate-y-0.5 transition-all">
                        ✨ Crear Publicación
                    </Link>
                    <Link v-if="activeTab === 'siguiendo'" :href="route('discover.index')" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border-2 border-gray-100 dark:border-gray-600 shadow-sm text-sm font-bold rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                        🔍 Descubrir Personas
                    </Link>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
    <DeletePostModal v-if="postToDelete" :post="postToDelete" @close="closeDeleteModal" />
</template>