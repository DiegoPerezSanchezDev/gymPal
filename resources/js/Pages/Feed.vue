// resources/js/Pages/Feed.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    posts: Object,
    title: String,
    activeTab: String, 
});

// Variable reactiva para almacenar la lista de todos los posts
const allPosts = ref(props.posts.data);

// Variable para controlar la URL de la siguiente página
let nextPageUrl = ref(props.posts.next_page_url);

function setActiveTab(tabName) {
    router.get(route('feed.index'), { tab: tabName }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            allPosts.value = page.props.posts.data;
            nextPageUrl.value = page.props.posts.next_page_url;
        }
    });
}

function loadMorePosts() {
    if (!nextPageUrl.value) return;

    router.get(nextPageUrl.value, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            allPosts.value = [...allPosts.value, ...page.props.posts.data];
            nextPageUrl.value = page.props.posts.next_page_url;
        },
    });
}
</script>

<template>
    <Head :title="title || 'Feed'" />

    <AuthenticatedLayout>
        <template #header_actions>
            <button class="text-gray-500 hover:text-indigo-600 p-1 rounded-full" title="Filtros del Feed">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.591 2.122l-2.67 1.335a2.25 2.25 0 01-2.447-2.122v-2.927a2.25 2.25 0 00-.659-1.591L4.659 7.409A2.25 2.25 0 014 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
            </button>
        </template>

        <!-- Sección de Pestañas (Tabs) -->
        <div class="bg-white shadow-sm md:hidden sticky top-16 z-30">
            <div class="max-w-7xl mx-auto">
                <nav class="flex border-b border-gray-200" aria-label="Tabs">
                    <button @click="setActiveTab('siguiendo')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-3 px-1 text-center border-b-2 font-medium text-sm whitespace-nowrap',
                                props.activeTab === 'siguiendo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        Siguiendo
                    </button>
                    <button @click="setActiveTab('populares')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-3 px-1 text-center border-b-2 font-medium text-sm whitespace-nowrap',
                                props.activeTab === 'populares' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        Populares
                    </button>
                    <button @click="setActiveTab('cerca')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-3 px-1 text-center border-b-2 font-medium text-sm whitespace-nowrap',
                                props.activeTab === 'cerca' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        Cerca
                    </button>
                </nav>
            </div>
        </div>

        <!-- Contenido del Feed -->
        <div class="container mx-auto px-2 sm:px-4 py-4 md:py-8">
            <h2 v-if="title" class="text-2xl font-bold text-gray-800 mb-6 hidden md:block text-center">{{ title }}</h2>

            <!-- Lista de Posts -->
            <div v-if="allPosts.length > 0">
                <div class="space-y-6">
                    <PostCard v-for="postItem in allPosts" :key="postItem.id" :post="postItem" />
                </div>

                <!-- Botón para Cargar Más -->
                <div v-if="nextPageUrl" class="text-center mt-8">
                    <button @click="loadMorePosts" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                        Cargar más
                    </button>
                </div>
            </div>
            
            <!-- Estado Vacío -->
            <div v-else class="text-center py-10 max-w-lg mx-auto">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tu feed está un poco silencioso</h3>
                <p class="mt-1 text-sm text-gray-500">Crea tu primera publicación o conecta con otros GymPals para ver su contenido aquí.</p>
                <div class="mt-6">
                    <Link :href="route('posts.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Crear Primera Publicación
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>