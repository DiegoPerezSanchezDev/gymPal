<!-- En resources/js/Components/DeletePostModal.vue -->
<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ post: Object });
const emit = defineEmits(['close']);

const isDeleting = ref(false);

const deletePost = () => {
    isDeleting.value = true;
    
    router.delete(route('posts.destroy', { post: props.post.id }), {
        onSuccess: () => {
            emit('close');
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};
</script>
<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 dark:bg-opacity-60 z-50 flex items-center justify-center p-4 transition-colors">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transition-colors">
            <h2 class="text-xl font-bold mb-2 text-gray-800 dark:text-white transition-colors">¿Confirmar Eliminación?</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6 transition-colors">
                Estás a punto de eliminar esta publicación de forma permanente.
                <strong class="text-red-600 dark:text-red-400 transition-colors">Esta acción no se puede deshacer.</strong>
            </p>
            <div class="flex justify-center gap-4">
                <button @click="$emit('close')" class="px-6 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold transition-colors">
                    Cancelar
                </button>
                <button @click="deletePost" :disabled="isDeleting" class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold disabled:opacity-50 transition-colors">
                    {{ isDeleting ? 'Eliminando...' : 'Eliminar' }}
                </button>
            </div>
        </div>
    </div>
</template>