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
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center">
            <h2 class="text-xl font-bold mb-2 text-gray-800">¿Confirmar Eliminación?</h2>
            <p class="text-gray-600 mb-6">
                Estás a punto de eliminar esta publicación de forma permanente.
                <strong class="text-red-600">Esta acción no se puede deshacer.</strong>
            </p>
            <div class="flex justify-center gap-4">
                <button @click="$emit('close')" class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 font-semibold">
                    Cancelar
                </button>
                <button @click="deletePost" :disabled="isDeleting" class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-50">
                    {{ isDeleting ? 'Eliminando...' : 'Eliminar' }}
                </button>
            </div>
        </div>
    </div>
</template>