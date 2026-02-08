<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    isLiked: {
        type: Boolean,
        default: false
    },
    isSaved: {
        type: Boolean,
        default: false
    },
    localLikesCount: {
        type: Number,
        default: 0
    },
    isProcessingLike: {
        type: Boolean,
        default: false
    },
    isProcessingSave: {
        type: Boolean,
        default: false
    },
    isDetailView: {
        type: Boolean,
        default: false
    },
    showCommentAction: {
        type: Boolean,
        default: true
    },
    heartPop: {
        type: Boolean,
        default: false
    }
});

defineEmits(['toggle-like', 'toggle-save']);
</script>

<template>
    <div class="px-4 py-3 flex justify-between items-center border-t border-gray-100 dark:border-gray-700 gap-4 transition-colors">
        
        <!-- Botón Me Gusta -->
        <button 
            @click="$emit('toggle-like')" 
            :disabled="isProcessingLike"
            class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all duration-200 group disabled:opacity-70" 
            :class="{ 'text-red-500': isLiked, 'text-gray-500 dark:text-gray-400': !isLiked }"
        >
            <svg 
                class="w-6 h-6 transition-transform duration-200"
                :class="[{'transform scale-125': heartPop, 'fill-red-500': isLiked}, isLiked ? '' : 'group-hover:scale-110']"
                :fill="isLiked ? 'currentColor' : 'none'"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 010-6.364z"></path>
            </svg>
            <span class="font-bold text-sm" :class="isLiked ? 'text-red-600' : 'text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200'">
                {{ localLikesCount > 0 ? localLikesCount : 'Me gusta' }}
            </span>
        </button>

        <!-- Botón Comentar -->
        <Link v-if="!isDetailView || showCommentAction" :href="route('posts.show', post.id)" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all duration-200 group text-gray-500 dark:text-gray-400">
            <svg class="w-6 h-6 transition-transform duration-200 group-hover:scale-110 text-gray-500 dark:text-gray-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            <span class="font-bold text-sm text-gray-600 dark:text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ post.comments_count > 0 ? post.comments_count : 'Comentar' }}</span>
        </Link>
        
        <!-- Botón Guardar -->
        <button 
            @click="$emit('toggle-save')" 
            :disabled="isProcessingSave"
            class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all duration-200 group disabled:opacity-70" 
            :class="isSaved ? 'text-yellow-500' : 'text-gray-500 dark:text-gray-400'"
            :title="isSaved ? 'Guardado' : 'Guardar'"
        >
            <svg 
                class="w-6 h-6 transition-transform duration-200 group-hover:scale-110" 
                :class="isSaved ? 'fill-yellow-500' : 'fill-none'"
                stroke="currentColor" 
                viewBox="0 0 24 24"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
            <span class="font-bold text-sm" :class="isSaved ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400 group-hover:text-yellow-600 dark:group-hover:text-yellow-400'">
                {{ isSaved ? 'Guardado' : 'Guardar' }}
            </span>
        </button>
    </div>
</template>
