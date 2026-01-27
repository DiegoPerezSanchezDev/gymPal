<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import PostSkeleton from '@/Components/Skeletons/PostSkeleton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import DeletePostModal from '@/Components/DeletePostModal.vue';
import StoryBar from '@/Components/StoryBar.vue';

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
const loadMoreTrigger = ref(null);

// Variable para controlar la URL de la siguiente página
let nextPageUrl = ref(props.posts.next_page_url);

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && nextPageUrl.value && !isLoadingMore.value && !isLoading.value) {
            loadMorePosts();
        }
    }, { threshold: 0.1 });

    if (loadMoreTrigger.value) {
        observer.observe(loadMoreTrigger.value);
    }
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
    if (!nextPageUrl.value || isLoadingMore.value) return;

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
            
            <!-- Banner de Completar Perfil (Si se saltó el onboarding) -->
            <div v-if="$page.props.auth.user.onboarding_skipped && !$page.props.auth.user.onboarding_completed" 
                 class="max-w-2xl mx-auto mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                <!-- Decoración -->
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-500"></div>
                
                <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-center sm:text-left">
                        <h3 class="text-xl font-black text-white mb-1 leading-tight tracking-tight">¡Tu perfil está incompleto! 🏋️‍♂️</h3>
                        <p class="text-indigo-100 text-sm font-medium">Completa tu información para conectar con otros GymPals y encontrar tu gimnasio.</p>
                    </div>
                    <Link :href="route('profile.edit')" 
                          class="whitespace-nowrap px-6 py-3 bg-white text-indigo-600 font-extrabold rounded-xl shadow-lg hover:bg-indigo-50 transform hover:-translate-y-0.5 transition-all text-sm">
                        Completar ahora
                    </Link>
                </div>
            </div>

            <h2 v-if="title" class="text-2xl font-bold text-gray-800 dark:text-white mb-6 hidden md:block text-center transition-colors">{{ title }}</h2>

            <!-- Barra de Historias (Oculta por petición) -->

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

                <!-- Trigger para Cargar Más (Intersection Observer) -->
                <div ref="loadMoreTrigger" class="h-20 flex items-center justify-center mt-4 mb-20">
                    <div v-if="isLoadingMore" class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Cargando más...</span>
                    </div>
                </div>
            </div>
        
            <!-- Estado Vacío o Sin GPS -->
            <div v-else class="text-center py-12 max-w-lg mx-auto bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 p-8 transition-colors">
                <!-- Caso 1: Sin GPS en pestaña Cerca -->
                <template v-if="activeTab === 'cerca' && (!$page.props.auth.user.latitude || !$page.props.auth.user.longitude)">
                    <div class="w-16 h-16 bg-amber-50 dark:bg-amber-900/30 text-amber-500 dark:text-amber-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl transition-colors">
                        📍
                    </div>
                    
                    <h3 class="mt-2 text-xl font-bold text-gray-900 dark:text-white transition-colors">
                        Ubicación necesaria
                    </h3>
                    
                    <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-sm mx-auto transition-colors">
                        Para mostrarte GymPals y actividad cerca de ti, necesitamos conocer tu ubicación. Completa tu perfil con tu ciudad para activar esta función.
                    </p>

                    <div class="mt-8">
                        <Link :href="route('profile.edit')" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent shadow-lg text-sm font-bold rounded-xl text-white bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 transform hover:-translate-y-0.5 transition-all">
                            📍 Configurar Ubicación
                        </Link>
                    </div>
                </template>

                <!-- Caso 2: Pestaña vacía normal -->
                <template v-else>
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
                            (activeTab === 'cerca' ? 'Parece que no hay actividad cerca. ¡Busca tu gimnasio en el mapa y únete a su comunidad!' : 'Sigue a más atletas o encuentra tu gimnasio para ver actividad aquí.') 
                        }}
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <Link :href="route('posts.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent shadow-lg text-sm font-bold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transform hover:-translate-y-0.5 transition-all">
                            ✨ Crear Publicación
                        </Link>
                        <Link :href="route('discover.index')" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border-2 border-gray-100 dark:border-gray-600 shadow-sm text-sm font-bold rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                            🗺️ Explorar Mapa y Gimnasios
                        </Link>
                    </div>
                </template>
            </div>

        </div>
    </AuthenticatedLayout>
    <DeletePostModal v-if="postToDelete" :post="postToDelete" @close="closeDeleteModal" />
</template>