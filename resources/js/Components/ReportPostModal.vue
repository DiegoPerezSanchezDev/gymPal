<!-- En resources/js/Components/ReportPostModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ post: Object });
const emit = defineEmits(['close']);

const form = useForm({
    reason: 'Contenido inapropiado', // Valor por defecto
    description: '',
});

const reportReasons = [
    'Contenido inapropiado',
    'Spam o publicidad',
    'Acoso o discurso de odio',
    'Información falsa',
    'Otro',
];

const submitReport = () => {
    form.post(route('posts.report', { post: props.post.id }), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>
<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative">
            <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            <h2 class="text-xl font-bold mb-4 pb-3 border-b border-gray-200">Denunciar Publicación</h2>
            <form @submit.prevent="submitReport">
                <div class="space-y-4">
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Motivo de la denuncia</label>
                        <select id="reason" v-model="form.reason" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option v-for="reason in reportReasons" :key="reason" :value="reason">{{ reason }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
                        <textarea id="description" v-model="form.description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Añade más detalles si es necesario..."></textarea>
                        <div v-if="form.errors.reason" class="text-sm text-red-600 mt-1">{{ form.errors.reason }}</div>
                    </div>
                </div>
                <div class="flex justify-end pt-4 mt-4 border-t border-gray-200">
                    <button type="submit" :disabled="form.processing" class="bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-300 text-white rounded-lg px-6 py-2 font-semibold transition-colors duration-200 shadow disabled:opacity-70">
                        {{ form.processing ? 'Enviando...' : 'Enviar Denuncia' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>