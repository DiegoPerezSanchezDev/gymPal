// resources/js/Pages/Feed.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({
    posts: Array,
    title: String
});

const activeTab = ref('siguiendo');


const setActiveTab = (tabName) => {
    activeTab.value = tabName;
};
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
                                activeTab === 'siguiendo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        Siguiendo
                    </button>
                    <button @click="setActiveTab('populares')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-3 px-1 text-center border-b-2 font-medium text-sm whitespace-nowrap',
                                activeTab === 'populares' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        Populares
                    </button>
                    <button @click="setActiveTab('cerca')"
                            :class="[
                                'flex-1 group inline-flex items-center justify-center py-3 px-1 text-center border-b-2 font-medium text-sm whitespace-nowrap',
                                activeTab === 'cerca' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        Cerca
                    </button>
                </nav>
            </div>
        </div>

        <!-- Contenido del Feed -->
        <div class="container mx-auto px-2 sm:px-4 py-4 md:py-8">
            <h2 v-if="title" class="text-2xl font-semibold text-gray-800 mb-4 hidden md:block">{{ title }}</h2>

            <div v-if="displayedPosts && displayedPosts.length > 0">
                <div class="space-y-4">
                    <PostCard v-for="postItem in displayedPosts" :key="postItem.id" :post="postItem" />
                </div>
            </div>
            <div v-else class="text-center py-10">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay publicaciones todavía</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Cuando sigas a gente o se creen publicaciones, aparecerán aquí.
                </p>
                <div class="mt-6">
                    <Link :href="route('posts.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Crear Primera Publicación
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>