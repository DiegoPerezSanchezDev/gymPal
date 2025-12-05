<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import PostCard from '../../Components/PostCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

const newCommentBody = ref("");
const submitting = ref(false);
const localComments = ref(props.post.comments || []);

const submitComment = async () => {
    if (!newCommentBody.value.trim()) return;
    submitting.value = true;
    try {
        const response = await axios.post('/comments', {
            post_id: props.post.id,
            body: newCommentBody.value,
        });
        localComments.value.unshift(response.data);
        newCommentBody.value = "";
    } catch (err) {
        console.error('Error al enviar comentario', err);
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-8 bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header Navegación -->
                <div class="mb-6 flex items-center justify-between">
                    <Link :href="route('feed.index')" class="group flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors font-medium">
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center group-hover:border-indigo-200 dark:group-hover:border-indigo-600 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/30 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </div>
                        Volver al Feed
                    </Link>
                </div>

                <!-- Post Card -->
                <div class="mb-6">
                    <PostCard :post="props.post" :isDetailView="true" :showCommentAction="false" />
                </div>

                <!-- Sección de Comentarios -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
                    <div class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 transition-colors">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 transition-colors">
                            Comentarios 
                            <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs px-2 py-1 rounded-full transition-colors">{{ localComments.length }}</span>
                        </h2>
                    </div>

                    <!-- Formulario de Comentarios -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 transition-colors">
                        <form @submit.prevent="submitComment" class="flex gap-4">
                            <img 
                                :src="$page.props.auth.user.profile_picture_url ? '/storage/' + $page.props.auth.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + $page.props.auth.user.name + '&background=random'" 
                                class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 transition-colors"
                            />
                            <div class="flex-1">
                                <textarea 
                                    v-model="newCommentBody"
                                    rows="2"
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all resize-none text-sm"
                                    placeholder="Escribe un comentario..."
                                    :disabled="submitting"
                                ></textarea>
                                <div class="flex justify-end mt-2">
                                    <button 
                                        type="submit" 
                                        class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="!newCommentBody.trim() || submitting"
                                    >
                                        {{ submitting ? 'Enviando...' : 'Comentar' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de Comentarios -->
                    <div class="divide-y divide-gray-50">
                        <div v-if="localComments.length > 0">
                            <div v-for="comment in localComments" :key="comment.id" class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex gap-4">
                                    <Link :href="route('profile.show.public', { user: comment.user.username })">
                                        <img 
                                            :src="comment.user && comment.user.profile_picture_url ? '/storage/' + comment.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + (comment.user ? comment.user.name : 'Anon') + '&background=random'" 
                                            class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 hover:ring-indigo-200 dark:hover:ring-indigo-600 transition-all"
                                        />
                                    </Link>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <Link :href="route('profile.show.public', { user: comment.user.username })" class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-sm">
                                                {{ comment.user ? comment.user.name : 'Anónimo' }}
                                            </Link>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 transition-colors">{{ new Date(comment.created_at).toLocaleDateString() }}</span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed whitespace-pre-line transition-colors">{{ comment.body }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors">
                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium transition-colors">No hay comentarios aún</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm transition-colors">Sé el primero en comentar esta publicación</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>