<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PostActionsMenu from './PostActionsMenu.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    isOwner: {
        type: Boolean,
        default: false
    },
    formattedDate: {
        type: String,
        required: true
    },
    formattedDistance: {
        type: String,
        default: null
    }
});

defineEmits(['report-post', 'delete-post', 'share-post']);
</script>

<template>
    <div class="p-4 flex items-center justify-between">
        <div class="flex items-center">
            <Link :href="route('profile.show.public', { user: post.user.username })" class="flex-shrink-0">
                <img
                    :src="post.user.profile_picture_url ? '/storage/' + post.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + post.user.name + '&background=random'"
                    alt="Avatar del usuario"
                    class="w-11 h-11 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 transition-all"
                />
            </Link>
            <div class="ml-3 flex-1 overflow-hidden">
                <div class="flex items-center gap-2">
                    <Link :href="route('profile.show.public', { user: post.user.username })" class="font-bold text-sm text-gray-800 dark:text-white hover:underline transition-colors truncate">
                        {{ post.user.name }}
                    </Link>
                    <!-- Badge de Relación -->
                    <span v-if="post.user.is_gym_pal" class="px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-900/40 text-[9px] font-black text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 transition-colors uppercase tracking-widest shrink-0">
                        GymPal
                    </span>
                    <span v-else-if="post.user.is_following" class="px-1.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/40 text-[9px] font-black text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800 transition-colors uppercase tracking-widest shrink-0">
                        Siguiendo
                    </span>
                    <span v-else-if="post.user.is_following_me" class="px-1.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-[9px] font-black text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 transition-colors uppercase tracking-widest shrink-0">
                        Te sigue
                    </span>
                </div>
                <div class="flex items-center gap-2 flex-wrap min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors shrink-0">{{ formattedDate }}</p>
                    <div v-if="formattedDistance" class="flex items-center gap-1.5 text-[11px] font-black text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-full transition-all border border-indigo-100 dark:border-indigo-800 shadow-sm shrink-0">
                        <svg class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ formattedDistance }}</span>
                    </div>
                    
                    <!-- GYM BADGE -->
                    <Link 
                        v-if="post.user.gyms && post.user.gyms.length > 0"
                        :href="route('discover.index', { search: post.user.gyms[0].name })"
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full flex items-center gap-1 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors shrink-0"
                        title="Ver gimnasio"
                    >
                        <span class="text-[10px]">{{ post.user.gyms[0].type === 'pool' ? '🏊' : (post.user.gyms[0].type === 'yoga' ? '🧘' : (post.user.gyms[0].type === 'crossfit' ? '🔥' : (post.user.gyms[0].type === 'park' ? '🤸' : '🏋️'))) }}</span>
                        <span class="truncate max-w-[80px] text-[10px]">{{ post.user.gyms[0].name }}</span>
                    </Link>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button 
                @click="$emit('share-post')" 
                class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group"
                title="Compartir"
            >
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-green-500 dark:group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </button>
            <PostActionsMenu 
                :post="post" 
                :isOwner="isOwner"
                @report-post="$emit('report-post')"
                @delete-post="$emit('delete-post')"
            />
        </div>
    </div>
</template>
