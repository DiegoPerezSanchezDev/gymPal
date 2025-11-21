<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({
    title: String
});

const user = computed(() => usePage().props.auth.user);
const avatarUrl = computed(() => {
    if (user.value.profile_picture_url) {
        return `/storage/${user.value.profile_picture_url}`;
    }
    const name = user.value.display_name || user.value.name || 'User';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&color=fff&size=128`;
});

const form = useForm({
    content: '',
    image: null,
});

const imagePreviewUrl = ref(null);
const fileInput = ref(null);

function triggerFileInput() {
    fileInput.value.click();
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviewUrl.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    form.image = null;
    imagePreviewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

const submit = () => {
    form.post(route('posts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            imagePreviewUrl.value = null;
        },
    });
};
</script>

<template>
    <Head :title="title || 'Crear Publicación'" />

    <AuthenticatedLayout>
        <div class="py-6 md:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Card Principal -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-indigo-50 to-purple-50">
                        <div class="flex items-center gap-3">
                            <Link :href="route('feed.index')" class="text-gray-500 hover:text-gray-700 p-2 -ml-2 rounded-full hover:bg-white/50 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </Link>
                            <h1 class="text-xl font-extrabold text-gray-900">✨ Crear Publicación</h1>
                        </div>
                        <button 
                            @click="submit"
                            :disabled="form.processing || (!form.content && !form.image)"
                            class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-bold rounded-full shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105"
                        >
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Publicando...
                            </span>
                            <span v-else>Publicar</span>
                        </button>
                    </div>

                    <!-- Área de Contenido -->
                    <div class="p-6">
                        <!-- Info Usuario -->
                        <div class="flex items-center gap-3 mb-6">
                            <img :src="avatarUrl" alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-indigo-100 shadow-sm">
                            <div>
                                <p class="font-bold text-gray-900">{{ user.display_name || user.name }}</p>
                                <div class="flex items-center text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full w-fit mt-1">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    🌍 Público
                                </div>
                            </div>
                        </div>

                        <!-- Textarea -->
                        <textarea
                            v-model="form.content"
                            class="w-full border-none focus:ring-0 text-lg placeholder-gray-400 resize-none p-0 min-h-[180px] font-medium"
                            :placeholder="`¿Qué estás pensando, ${user.name.split(' ')[0]}?`"
                            autofocus
                        ></textarea>

                        <!-- Previsualización de Imagen -->
                        <div v-if="imagePreviewUrl" class="relative mt-6 rounded-xl overflow-hidden group shadow-md border border-gray-200">
                            <img :src="imagePreviewUrl" class="w-full h-auto max-h-96 object-cover">
                            <button 
                                @click="removeImage"
                                class="absolute top-3 right-3 p-2 bg-black/70 text-white rounded-full hover:bg-black/90 transition-all backdrop-blur-sm shadow-lg transform hover:scale-110"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Error Messages -->
                        <div v-if="form.errors.content" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-600 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                {{ form.errors.content }}
                            </p>
                        </div>
                    </div>

                    <!-- Barra de Herramientas Inferior -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="triggerFileInput"
                                    type="button"
                                    class="p-2.5 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all flex items-center gap-2 group border border-transparent hover:border-indigo-200"
                                    title="Añadir foto"
                                >
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm font-bold hidden sm:inline">Foto</span>
                                </button>
                                
                                <!-- Etiquetar Personas (Próximamente) -->
                                <button 
                                    type="button"
                                    class="p-2.5 text-gray-400 hover:text-indigo-400 hover:bg-indigo-50 rounded-xl transition-all flex items-center gap-2 group border border-transparent hover:border-indigo-100 cursor-not-allowed opacity-60" 
                                    title="Etiquetar personas (Próximamente)" 
                                    disabled
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-sm font-bold hidden sm:inline">Etiquetar</span>
                                </button>
                                
                                <!-- Ubicación (Próximamente) -->
                                <button 
                                    type="button"
                                    class="p-2.5 text-gray-400 hover:text-purple-400 hover:bg-purple-50 rounded-xl transition-all flex items-center gap-2 group border border-transparent hover:border-purple-100 cursor-not-allowed opacity-60" 
                                    title="Añadir ubicación (Próximamente)" 
                                    disabled
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-sm font-bold hidden sm:inline">Ubicación</span>
                                </button>
                            </div>

                            <div class="text-xs text-gray-400 font-medium">
                                {{ form.content.length }}/5000
                            </div>
                        </div>
                        
                        <input 
                            type="file" 
                            ref="fileInput" 
                            class="hidden" 
                            accept="image/*" 
                            @change="handleImageUpload"
                        >
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>