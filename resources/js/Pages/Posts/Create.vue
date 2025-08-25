// resources/js/Pages/Posts/Create.vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
// Podrías necesitar un componente de Input o Textarea si los tienes personalizados
// import TextInput from '@/Components/TextInput.vue';
// import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    title: String
});

const form = useForm({
    content: '',
    image: null, // Para el archivo de imagen
});

const imagePreviewUrl = ref(null);

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        form.image = file; // Almacena el archivo en el objeto form de Inertia
        // Crear URL para previsualización
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviewUrl.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        form.image = null;
        imagePreviewUrl.value = null;
    }
}

function removeImage() {
    form.image = null;
    imagePreviewUrl.value = null;
    // Resetear el input file para poder seleccionar la misma imagen de nuevo si se desea
    const inputFile = document.getElementById('imageUploadInput');
    if (inputFile) {
        inputFile.value = '';
    }
}

const submit = () => {
    // Inertia maneja automáticamente el 'multipart/form-data' cuando hay un objeto File
    form.post(route('posts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset(); // Limpia el formulario
            imagePreviewUrl.value = null; // Limpia la previsualización
            // Opcional: mostrar un toast/notificación de éxito
            // Opcional: Inertia.visit(route('feed.index')) si quieres redirigir siempre
        },
        onError: (errors) => {
            // Los errores de validación estarán disponibles en form.errors
            console.error('Errores al crear el post:', errors);
        }
    });
};
</script>

<template>
    <Head :title="title || 'Crear Publicación'" />

    <AuthenticatedLayout>
        <template #header_actions>
            <!-- No suelen haber acciones específicas en el header para "Crear Post" -->
            <!-- El botón de "Publicar" está en el cuerpo principal -->
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white shadow-md rounded-lg p-6 space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Nueva Publicación</h2>
                        <textarea
                            v-model="form.content"
                            rows="5"
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1"
                            placeholder="¿Qué tienes en mente, GymPal?"
                        ></textarea>
                        <div v-if="form.errors.content" class="text-sm text-red-600 mt-1">{{ form.errors.content }}</div>
                    </div>

                    <div>
                        <label for="imageUploadInput" class="block text-sm font-medium text-gray-700 mb-1">
                            Añadir Imagen (Opcional)
                        </label>
                        <input
                            id="imageUploadInput"
                            type="file"
                            @change="handleImageUpload"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100"
                            accept="image/png, image/jpeg, image/gif"
                        />
                        <div v-if="form.errors.image" class="text-sm text-red-600 mt-1">{{ form.errors.image }}</div>

                        <div v-if="imagePreviewUrl" class="mt-4 relative">
                            <img :src="imagePreviewUrl" alt="Previsualización de imagen" class="rounded-md max-h-96 object-contain mx-auto">
                            <button @click="removeImage" type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <Link :href="route('feed.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || (!form.content && !form.image)"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Publicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>