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

const reasonOptions = reportReasons.map(r => ({ value: r, label: r }));

const submitReport = () => {
    form.post(route('posts.report', { post: props.post.id }), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>
<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 dark:bg-opacity-60 z-50 flex items-center justify-center p-4 transition-colors">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 relative transition-colors">
            <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl transition-colors">&times;</button>
            <h2 class="text-xl font-bold mb-4 pb-3 border-b border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white transition-colors">Denunciar Publicación</h2>
            <form @submit.prevent="submitReport">
                <div class="space-y-4">
                    <div>
                        <SelectInput
                            id="reason"
                            label="Motivo de la denuncia"
                            v-model="form.reason"
                            :options="reasonOptions"
                        />
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 transition-colors">Descripción (opcional)</label>
                        <textarea 
                            id="description" 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:focus:ring-indigo-900 transition-colors" 
                            placeholder="Añade más detalles si es necesario..."
                        ></textarea>
                        <div v-if="form.errors.reason" class="text-sm text-red-600 dark:text-red-400 mt-1 transition-colors">{{ form.errors.reason }}</div>
                    </div>
                </div>
                <div class="flex justify-end pt-4 mt-4 border-t border-gray-200 dark:border-gray-700 transition-colors">
                    <button type="submit" :disabled="form.processing" class="bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-300 text-white rounded-lg px-6 py-2 font-semibold transition-colors duration-200 shadow disabled:opacity-70">
                        {{ form.processing ? 'Enviando...' : 'Enviar Denuncia' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>