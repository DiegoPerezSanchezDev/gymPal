<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({
    title: String,
    userWorkouts: Array
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
    workout_id: null,
});

const imagePreviewUrl = ref(null);
const fileInput = ref(null);
const showWorkoutModal = ref(false);
const selectedWorkout = ref(null);

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

function selectWorkout(workout) {
    selectedWorkout.value = workout;
    form.workout_id = workout.id;
    showWorkoutModal.value = false;
}

function removeWorkout() {
    selectedWorkout.value = null;
    form.workout_id = null;
}

const submit = () => {
    form.post(route('posts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            imagePreviewUrl.value = null;
            selectedWorkout.value = null;
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
                            :disabled="form.processing || (!form.content && !form.image && !form.workout_id)"
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

                        <!-- Previsualización de Rutina Seleccionada -->
                        <div v-if="selectedWorkout" class="mt-6 p-4 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border-2 border-indigo-200 relative group">
                            <button 
                                @click="removeWorkout"
                                class="absolute top-3 right-3 p-1.5 bg-white text-indigo-600 rounded-full hover:bg-red-50 hover:text-red-600 transition-all shadow-md transform hover:scale-110"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                    <span class="text-2xl">💪</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-indigo-600 font-bold uppercase tracking-wider mb-1">Rutina adjunta</p>
                                    <h4 class="font-bold text-gray-900 mb-1">{{ selectedWorkout.name }}</h4>
                                    <p v-if="selectedWorkout.description" class="text-sm text-gray-600 line-clamp-2">{{ selectedWorkout.description }}</p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-xs bg-white px-2 py-1 rounded-full font-semibold text-indigo-600">{{ selectedWorkout.category }}</span>
                                        <span class="text-xs text-gray-500">{{ selectedWorkout.exercises_count || 0 }} ejercicios</span>
                                    </div>
                                </div>
                            </div>
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
                                
                                <!-- Adjuntar Rutina -->
                                <button 
                                    @click="showWorkoutModal = true"
                                    type="button"
                                    class="p-2.5 text-purple-600 hover:bg-purple-50 rounded-xl transition-all flex items-center gap-2 group border border-transparent hover:border-purple-200"
                                    title="Adjuntar rutina"
                                >
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <span class="text-sm font-bold hidden sm:inline">Rutina</span>
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

        <!-- Modal de Selección de Rutinas -->
        <div v-if="showWorkoutModal" @click.self="showWorkoutModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[80vh] overflow-hidden shadow-2xl animate-fadeIn">
                <!-- Header del Modal -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                                <span class="text-xl">💪</span>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-900">Seleccionar Rutina</h2>
                                <p class="text-xs text-gray-500">Elige una rutina para adjuntar a tu publicación</p>
                            </div>
                        </div>
                        <button @click="showWorkoutModal = false" class="p-2 hover:bg-white/50 rounded-full transition">
                            <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Lista de Rutinas -->
                <div class="p-6 overflow-y-auto max-h-[calc(80vh-120px)] custom-scrollbar">
                    <div v-if="userWorkouts && userWorkouts.length > 0" class="space-y-3">
                        <button 
                            v-for="workout in userWorkouts" 
                            :key="workout.id"
                            @click="selectWorkout(workout)"
                            class="w-full p-4 bg-white hover:bg-gradient-to-br hover:from-indigo-50 hover:to-purple-50 rounded-xl border-2 border-gray-100 hover:border-indigo-300 transition-all text-left group"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <span class="text-2xl">🏋️</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">{{ workout.name }}</h3>
                                    <p v-if="workout.description" class="text-sm text-gray-600 line-clamp-1 mt-0.5">{{ workout.description }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs bg-gray-100 group-hover:bg-white px-2 py-1 rounded-full font-semibold text-gray-600">{{ workout.category }}</span>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-600 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-4xl">📝</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">No tienes rutinas</h3>
                        <p class="text-gray-500 mb-4">Crea tu primera rutina para poder adjuntarla a tus posts</p>
                        <Link :href="route('workouts.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Crear Rutina
                        </Link>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: scale(0.95); 
    }
    to { 
        opacity: 1; 
        transform: scale(1); 
    }
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>