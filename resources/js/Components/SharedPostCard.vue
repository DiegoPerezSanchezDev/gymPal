<!-- Componente para mostrar un post compartido en el chat -->
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    postData: {
        type: Object,
        required: true,
    },
});

// Formatear fecha del post
const formattedDate = computed(() => {
    if (!props.postData.post_created_at) return '';
    return new Date(props.postData.post_created_at).toLocaleString('es-ES', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
});
</script>

<template>
    <Link 
        :href="postData.post_url || route('posts.show', postData.post_id)" 
        class="block bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden"
    >
        <!-- Header del post compartido -->
        <div class="p-3 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
            </svg>
            <span class="text-xs text-gray-600 font-medium">Publicación compartida</span>
        </div>

        <!-- Contenido del post -->
        <div class="p-3">
            <div class="flex items-center gap-2 mb-2">
                <img
                    :src="postData.post_author_profile_picture ? `/storage/${postData.post_author_profile_picture}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(postData.post_author_name || 'Usuario')}&background=random`"
                    :alt="postData.post_author_name"
                    class="w-8 h-8 rounded-full object-cover"
                />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-800 truncate">
                        {{ postData.post_author_name }}
                    </p>
                    <p class="text-xs text-gray-500">{{ formattedDate }}</p>
                </div>
            </div>

            <!-- Texto del post -->
            <p 
                v-if="postData.post_content" 
                class="text-sm text-gray-700 mb-2 line-clamp-3 whitespace-pre-line"
            >
                {{ postData.post_content }}
            </p>

            <!-- Rutina adjunta (si existe) -->
            <div 
                v-if="postData.workout" 
                class="mt-2 mb-2 rounded-lg p-3 border"
                :class="{
                    'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-800': postData.workout.difficulty === 'Principiante',
                    'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-blue-200 dark:border-blue-800': postData.workout.difficulty === 'Intermedio',
                    'bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-purple-200 dark:border-purple-800': postData.workout.difficulty === 'Avanzado',
                    'bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border-indigo-200 dark:border-indigo-800': !postData.workout.difficulty || !['Principiante', 'Intermedio', 'Avanzado'].includes(postData.workout.difficulty)
                }"
            >
                <div class="flex items-start gap-2">
                    <div 
                        class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                        :class="{
                            'bg-green-500': postData.workout.difficulty === 'Principiante',
                            'bg-blue-500': postData.workout.difficulty === 'Intermedio',
                            'bg-purple-500': postData.workout.difficulty === 'Avanzado',
                            'bg-indigo-500': !postData.workout.difficulty || !['Principiante', 'Intermedio', 'Avanzado'].includes(postData.workout.difficulty)
                        }"
                    >
                        <span class="text-white text-sm">🏋️</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p 
                            class="font-bold text-sm truncate"
                            :class="{
                                'text-green-900 dark:text-green-200': postData.workout.difficulty === 'Principiante',
                                'text-blue-900 dark:text-blue-200': postData.workout.difficulty === 'Intermedio',
                                'text-purple-900 dark:text-purple-200': postData.workout.difficulty === 'Avanzado',
                                'text-indigo-900 dark:text-indigo-200': !postData.workout.difficulty || !['Principiante', 'Intermedio', 'Avanzado'].includes(postData.workout.difficulty)
                            }"
                        >
                            {{ postData.workout.name }}
                        </p>
                        <div 
                            class="flex items-center gap-3 mt-1 text-xs"
                            :class="{
                                'text-green-600 dark:text-green-400': postData.workout.difficulty === 'Principiante',
                                'text-blue-600 dark:text-blue-400': postData.workout.difficulty === 'Intermedio',
                                'text-purple-600 dark:text-purple-400': postData.workout.difficulty === 'Avanzado',
                                'text-indigo-600 dark:text-indigo-400': !postData.workout.difficulty || !['Principiante', 'Intermedio', 'Avanzado'].includes(postData.workout.difficulty)
                            }"
                        >
                            <span v-if="postData.workout.difficulty" class="flex items-center gap-1">
                                <span class="font-medium">{{ postData.workout.difficulty }}</span>
                            </span>
                            <span v-if="postData.workout.duration_minutes" class="flex items-center gap-1">
                                ⏱️ {{ postData.workout.duration_minutes }}'
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imagen del post (si existe) -->
            <div v-if="postData.post_image_path" class="mt-2 rounded-md overflow-hidden">
                <img 
                    :src="`/storage/${postData.post_image_path}`" 
                    alt="Imagen de la publicación"
                    class="w-full h-auto max-h-48 object-cover"
                />
            </div>

            <!-- Footer con estadísticas -->
            <div class="mt-2 pt-2 border-t border-gray-100 flex items-center gap-4 text-xs text-gray-500">
                <div v-if="postData.post_likes_count > 0" class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ postData.post_likes_count }}</span>
                </div>
                <div class="flex items-center gap-1 text-indigo-600 font-medium">
                    <span>Ver publicación →</span>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

