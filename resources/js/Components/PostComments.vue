<script setup>
import { Link } from '@inertiajs/vue3';
import { useTimeAgo } from '@/composables/useTimeAgo';

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    isDetailView: {
        type: Boolean,
        default: false
    },
    submittingComment: {
        type: Boolean,
        default: false
    }
});

const { timeAgo } = useTimeAgo();

const emit = defineEmits(['submit-comment']);
const model = defineModel('newCommentBody');
</script>

<template>
    <div>
        <!-- Lista de Comentarios -->
        <div v-if="!isDetailView && post.latest_comments && post.latest_comments.length" class="px-5 pb-4 pt-4 space-y-3">
            <div class="mb-2 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider transition-colors">
                Comentarios recientes
            </div>
            <div v-for="comment in post.latest_comments" :key="comment.id" class="flex gap-3 group">
                <Link :href="comment.user ? route('profile.show.public', { user: comment.user.username }) : '#'" class="flex-shrink-0">
                    <img :src="comment.user && comment.user.profile_picture_url ? '/storage/' + comment.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + (comment.user ? comment.user.name : 'Anon') + '&background=random'" 
                        alt="Avatar" 
                        class="w-8 h-8 rounded-full object-cover ring-2 ring-transparent group-hover:ring-indigo-100 dark:group-hover:ring-indigo-900 transition-all" />
                </Link>
                <div class="flex-1 bg-gray-50 dark:bg-gray-700 rounded-2xl rounded-tl-none px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                    <div class="flex justify-between items-baseline">
                        <span class="font-bold text-gray-900 dark:text-white mr-2 transition-colors">{{ comment.user ? comment.user.name : 'Anon' }}</span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium uppercase transition-colors">{{ timeAgo(comment.created_at) }}</span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-0.5 transition-colors">{{ comment.body }}</p>
                </div>
            </div>
            
            <Link
                v-if="post.comments_count > (post.latest_comments.length || 0)"
                :href="route('posts.show', post.id)"
                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-semibold mt-2 block pl-11 hover:underline transition-colors"
            >
                Ver los {{ post.comments_count }} comentarios
            </Link>
        </div>


        <!-- Formulario para nuevo comentario -->
        <form v-if="!isDetailView" @submit.prevent="$emit('submit-comment')" class="px-5 pb-4">
            <div class="flex items-start gap-3">
                <img 
                    v-if="$page.props.auth && $page.props.auth.user"
                    :src="$page.props.auth.user.profile_picture_url ? '/storage/' + $page.props.auth.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + $page.props.auth.user.name + '&background=random'"
                    alt="Tu avatar"
                    class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-100 dark:ring-indigo-900 mt-1 transition-all duration-200"
                />
                <div class="flex-1">
                    <textarea
                        v-model="model"
                        rows="2"
                        placeholder="Escribe un comentario..."
                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 p-2 min-h-[45px] text-[15px] transition-colors duration-200 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 shadow resize-none"
                        :disabled="submittingComment"
                    ></textarea>
                    <div class="flex justify-end mt-1">
                        <button
                            type="submit"
                            class="bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-300 text-white rounded-lg px-5 py-2 font-semibold transition-colors duration-200 shadow disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="!model || !model.trim() || submittingComment"
                        >
                            Comentar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
