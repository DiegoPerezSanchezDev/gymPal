<script setup>
// --- IMPORTACIONES ---
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Componentes
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import TagsInput from '@/Components/TagsInput.vue';
import AvailabilityInput from '@/Components/AvailabilityInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CityAutocomplete from '@/Components/CityAutocomplete.vue';
import SelectInput from '@/Components/SelectInput.vue';
import MultiSelectInput from '@/Components/MultiSelectInput.vue';
import GymMapModal from '@/Components/GymMapModal.vue';
import { useToast } from '@/composables/useToast';

// --- PROPS ---
const props = defineProps({
    user: Object,
    interests: Array,
    mustVerifyEmail: Boolean,
    status: String,
    gyms: Array,
});

// --- DATOS GLOBALES ---
const geoapifyKey = usePage().props.geoapify_key;

// --- OPCIONES PARA LOS SELECTS ---
const allInterests = computed(() => props.interests || []);

const interestOptions = computed(() => {
    const options = [{ value: null, label: 'Cualquier actividad' }];
    if (Array.isArray(allInterests.value)) {
        allInterests.value.forEach(interest => {
            options.push({ value: interest.id, label: interest.name });
        });
    }
    return options;
});

const gymOptions = computed(() => {
    // Para multiselect, el formato debe ser value/label
    if (Array.isArray(props.gyms)) {
        return props.gyms.map(gym => ({
            value: gym.id,
            label: gym.address ? `${gym.name} - ${gym.address}` : gym.name
        }));
    }
    return [];
});

// --- ESTADO LOCAL ---
const locationStatus = ref('');
const avatarUrl = computed(() => {
    // Si hay preview, mostrar preview
    if (profilePicturePreview.value) {
        return profilePicturePreview.value;
    }
    // Si no, mostrar la foto actual o avatar generado
    if (props.user.profile_picture_url) {
        return `/storage/${props.user.profile_picture_url}`;
    }
    const nameForAvatar = props.user.display_name || props.user.name || 'Gym Pal';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(nameForAvatar)}&background=random&color=fff&size=256&font-size=0.33`;
});

// --- FORMULARIO ---
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    display_name: props.user.display_name || '',
    username: props.user.username || '',
    bio: props.user.bio || '',
    location_city: props.user.location_city || '',
    experience_level: props.user.experience_level || 'Principiante', 
    interests: props.user.fitness_interests ? props.user.fitness_interests.map(i => i.id) : [], 
    availability_general: props.user.availability_general || [],
    looking_for_interest_id: props.user.looking_for_interest_id || null, 
    latitude: props.user.latitude || null,
    longitude: props.user.longitude || null,
    gym_ids: props.user.gyms ? props.user.gyms.map(g => g.id) : [],
    profile_picture: null, // Nueva foto de perfil
    remove_profile_picture: false, // Flag para eliminar foto
});

// --- ESTADO PARA PREVIEW DE IMAGEN ---
const profilePicturePreview = ref(null);
const fileInputRef = ref(null);
const showRemovePhotoModal = ref(false);
const showLocationPermissionModal = ref(false);
const showGymMapModal = ref(false);

const { error: showError } = useToast();

// --- COMPUTED PROPERTY PARA LA UBICACIÓN ---
const hasLocationSaved = computed(() => form.latitude && form.longitude);

// --- FUNCIONES ---
function handleCitySelected(cityData) {
    form.location_city = cityData.name;
    form.latitude = cityData.lat;
    form.longitude = cityData.lon;
}

function getUserLocation() {
    locationStatus.value = 'Obteniendo ubicación...';
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = parseFloat(position.coords.latitude.toFixed(7));
            form.longitude = parseFloat(position.coords.longitude.toFixed(7));
            locationStatus.value = '¡Ubicación actualizada! Guarda los cambios para conservarla.';
        },
        () => { locationStatus.value = 'No se pudo obtener la ubicación.'; }
    );
}

function openMapView() {
    if (!form.latitude || !form.longitude) {
        showLocationPermissionModal.value = true;
        return;
    }
    showGymMapModal.value = true;
}

function handleGymToggle(gymId) {
    const index = form.gym_ids.indexOf(gymId);
    if (index === -1) {
        form.gym_ids.push(gymId);
    } else {
        form.gym_ids.splice(index, 1);
    }
}

function saveProfile() {
    // Usar POST con _method=PATCH para poder subir archivos
    form.transform((data) => ({
        ...data,
        _method: 'PATCH'
    })).post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            profilePicturePreview.value = null;
        },
    });
}

// --- FUNCIONES PARA FOTO DE PERFIL ---
function triggerFileInput() {
    fileInputRef.value?.click();
}

function handleProfilePictureChange(event) {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño (máx 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showError('La imagen no puede superar los 5MB');
            return;
        }
        
        // Validar tipo
        if (!file.type.startsWith('image/')) {
            showError('Solo se permiten archivos de imagen');
            return;
        }
        
        form.profile_picture = file;
        form.remove_profile_picture = false;
        
        // Crear preview
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePicturePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function openRemovePhotoModal() {
    showRemovePhotoModal.value = true;
}

function confirmRemovePhoto() {
    form.profile_picture = null;
    form.remove_profile_picture = true;
    profilePicturePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
    showRemovePhotoModal.value = false;
}
</script>

<template>
    <Head title="Editar Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                        Editar Perfil
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Actualiza tu información</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="saveProfile">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Columna Izquierda: Avatar y Datos Básicos -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Tarjeta de Avatar -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center text-center transition-colors">
                                <div class="relative group">
                                    <div @click="triggerFileInput" class="cursor-pointer">
                                        <img :src="avatarUrl" :alt="form.display_name" class="w-32 h-32 rounded-full object-cover border-4 border-indigo-100 shadow-lg group-hover:opacity-75 transition-all">
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full bg-black/40">
                                            <div class="text-white text-center">
                                                <svg class="w-8 h-8 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span class="text-xs font-bold">Cambiar foto</span>
                                            </div>
                                        </div>
                                    </div>
                                    <input 
                                        type="file" 
                                        ref="fileInputRef" 
                                        class="hidden" 
                                        accept="image/*" 
                                        @change="handleProfilePictureChange"
                                    >
                                </div>
                                
                                <!-- Botón para eliminar foto (solo si tiene foto) -->
                                <button 
                                    v-if="props.user.profile_picture_url || profilePicturePreview" 
                                    @click="openRemovePhotoModal"
                                    type="button"
                                    class="mt-3 text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Eliminar foto
                                </button>
                                <p v-else class="mt-3 text-xs text-gray-400 dark:text-gray-600 transition-colors">Click para añadir foto</p>
                                
                                <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-white transition-colors">{{ form.display_name || form.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">@{{ form.username || 'sin-usuario' }}</p>
                            </div>

                            <!-- Tarjeta de Ubicación -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 transition-colors">
                                    <span class="text-xl">📍</span> Ubicación
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="location_city" value="Ciudad Base" />
                                        <CityAutocomplete v-model="form.location_city" :api-key="geoapifyKey" @city-selected="handleCitySelected" class="mt-1"/>
                                        <InputError class="mt-2" :message="form.errors.location_city" />
                                    </div>

                                    <div class="pt-2 border-t border-gray-100">
                                        <div v-if="hasLocationSaved" class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800 transition-colors">
                                            <span class="text-xs font-semibold text-green-700 dark:text-green-400 flex items-center gap-1 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                GPS Activo
                                            </span>
                                            <button type="button" @click="getUserLocation" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">Actualizar</button>
                                        </div>
                                        <button v-else type="button" @click="getUserLocation" class="w-full mt-2 flex items-center justify-center px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Activar GPS
                                        </button>
                                        <p v-if="locationStatus" class="mt-2 text-xs text-center text-indigo-600 dark:text-indigo-400 animate-pulse transition-colors">{{ locationStatus }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta de Gimnasio -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 transition-colors">
                                    <span class="text-xl">🏋️</span> Mis Centros de Entrenamiento
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <MultiSelectInput
                                            id="gym_ids"
                                            label="Mis Centros"
                                            v-model="form.gym_ids"
                                            :options="gymOptions"
                                            placeholder="Selecciona tus centros..."
                                        />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Busca tu gimnasio, box de CrossFit o estudio (filtrado por tu ciudad)</p>
                                        <InputError class="mt-2" :message="form.errors.gym_ids" />
                                    </div>

                                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <button 
                                            type="button" 
                                            @click="openMapView" 
                                            class="w-full flex items-center justify-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                                            Buscar en Mapa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Detalles y Fitness -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Información Personal -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2 transition-colors">Información Personal</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <InputLabel for="name" value="Nombre Completo" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            </div>
                                            <TextInput id="name" type="text" class="mt-1 block w-full pl-10" v-model="form.name" required />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>
                                    <div>
                                        <InputLabel for="display_name" value="Nombre Público" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </div>
                                            <TextInput id="display_name" type="text" class="mt-1 block w-full pl-10" v-model="form.display_name" />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.display_name" />
                                    </div>
                                    <div>
                                        <InputLabel for="username" value="Usuario" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-bold">@</span>
                                            </div>
                                            <TextInput id="username" type="text" class="mt-1 block w-full pl-8" v-model="form.username" required />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.username" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <InputLabel for="bio" value="Sobre mí" />
                                        <textarea id="bio" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 resize-none transition-colors" v-model="form.bio" placeholder="¿Cuáles son tus objetivos? ¿Qué te motiva?"></textarea>
                                        <InputError class="mt-2" :message="form.errors.bio" />
                                    </div>
                                </div>
                            </div>

                            <!-- Perfil Fitness -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2 transition-colors">Perfil Fitness</h3>
                                
                                <div class="space-y-8">
                                    <!-- Nivel de Experiencia (Visual) -->
                                    <div>
                                        <InputLabel value="Nivel de Experiencia" class="mb-3" />
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div v-for="level in ['Principiante', 'Intermedio', 'Avanzado']" :key="level"
                                                 @click="form.experience_level = level"
                                                 class="cursor-pointer border rounded-xl p-4 text-center transition-all duration-200 relative overflow-hidden group"
                                                 :class="{
                                                     'border-green-500 bg-green-50 dark:bg-green-900/20 ring-1 ring-green-500': form.experience_level === 'Principiante' && level === 'Principiante',
                                                     'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-500': form.experience_level === 'Intermedio' && level === 'Intermedio',
                                                     'border-purple-500 bg-purple-50 dark:bg-purple-900/20 ring-1 ring-purple-500': form.experience_level === 'Avanzado' && level === 'Avanzado',
                                                     'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700': form.experience_level !== level
                                                 }">
                                                <div class="text-2xl mb-1 transform group-hover:scale-110 transition-transform">
                                                    {{ level === 'Principiante' ? '🌱' : (level === 'Intermedio' ? '⚡' : '🔥') }}
                                                </div>
                                                <span class="block font-bold text-sm" 
                                                    :class="{
                                                        'text-green-700 dark:text-green-400': form.experience_level === 'Principiante' && level === 'Principiante',
                                                        'text-blue-700 dark:text-blue-400': form.experience_level === 'Intermedio' && level === 'Intermedio',
                                                        'text-purple-700 dark:text-purple-400': form.experience_level === 'Avanzado' && level === 'Avanzado',
                                                        'text-gray-700 dark:text-gray-400': form.experience_level !== level
                                                    }">
                                                    {{ level }}
                                                </span>
                                                <div v-if="form.experience_level === level" class="absolute top-2 right-2">
                                                    <svg class="w-4 h-4" :class="{
                                                        'text-green-600': level === 'Principiante',
                                                        'text-blue-600': level === 'Intermedio',
                                                        'text-purple-600': level === 'Avanzado'
                                                    }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Intereses -->
                                    <div>
                                        <InputLabel value="Mis Intereses / Deportes" class="mb-2" />
                                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700 transition-colors">
                                            <TagsInput v-model="form.interests" :interests="allInterests" />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.interests" />
                                    </div>

                                    <!-- Buscando... -->
                                    <div>
                                        <SelectInput
                                            id="looking_for_interest"
                                            label="🤝 Busco compañero principalmente para..."
                                            v-model="form.looking_for_interest_id"
                                            :options="interestOptions"
                                        />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors">Esto ayudará a otros usuarios a encontrarte si buscan lo mismo.</p>
                                    </div>

                                    <!-- Disponibilidad -->
                                    <div>
                                        <InputLabel value="Disponibilidad Habitual" class="mb-2" />
                                        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-700 transition-colors">
                                            <AvailabilityInput v-model="form.availability_general" />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.availability_general" />
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex flex-col items-center justify-center gap-4 pt-6 border-t border-gray-100 dark:border-gray-700 transition-colors">
                                <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-green-600 dark:text-green-400 flex items-center bg-green-50 dark:bg-green-900/20 px-4 py-2 rounded-full transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        ¡Cambios guardados correctamente!
                                    </p>
                                </transition>
                                
                                <PrimaryButton :disabled="form.processing" class="px-12 py-3 text-base shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                                    <span v-if="form.processing" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Guardando...
                                    </span>
                                    <span v-else> Guardar Cambios</span>
                                </PrimaryButton>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de confirmación de eliminación de foto -->
        <ConfirmModal
            :show="showRemovePhotoModal"
            type="warning"
            title="¿Eliminar foto de perfil?"
            message="¿Estás seguro de que quieres eliminar tu foto de perfil? Se mostrará un avatar generado automáticamente."
            confirm-text="Sí, eliminar"
            cancel-text="Cancelar"
            @confirm="confirmRemovePhoto"
            @cancel="showRemovePhotoModal = false"
        />

        <!-- Modal de permisos de geolocalización -->
        <ConfirmModal
            :show="showLocationPermissionModal"
            type="info"
            title="📍 Permisos de Ubicación Requeridos"
            message="Para utilizar el mapa y buscar gimnasios cercanos, primero debes permitir el acceso a tu ubicación GPS. Haz clic en 'Activar GPS' en la sección de Ubicación."
            confirm-text="Entendido"
            :show-cancel="false"
            @confirm="showLocationPermissionModal = false"
        />

        <!-- Modal de mapa de gimnasios -->
        <GymMapModal
            :show="showGymMapModal"
            :user-lat="form.latitude"
            :user-lon="form.longitude"
            :gyms="gyms"
            :selected-gyms="form.gym_ids"
            @toggle-gym="handleGymToggle"
            @close="showGymMapModal = false"
        />
    </AuthenticatedLayout>
</template>