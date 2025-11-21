<script setup>
// --- IMPORTACIONES ---
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Componentes
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TagsInput from '@/Components/TagsInput.vue';
import AvailabilityInput from '@/Components/AvailabilityInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CityAutocomplete from '@/Components/CityAutocomplete.vue';
import SelectInput from '@/Components/SelectInput.vue';

// --- PROPS ---
const props = defineProps({
    user: Object,
    interests: Array,
    mustVerifyEmail: Boolean,
    status: String,
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
    profile_picture: null, // Nueva foto de perfil
    remove_profile_picture: false, // Flag para eliminar foto
});

// --- ESTADO PARA PREVIEW DE IMAGEN ---
const profilePicturePreview = ref(null);
const fileInputRef = ref(null);

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
            alert('La imagen no puede superar los 5MB');
            return;
        }
        
        // Validar tipo
        if (!file.type.startsWith('image/')) {
            alert('Solo se permiten archivos de imagen');
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

function removeProfilePicture() {
    if (confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')) {
        form.profile_picture = null;
        form.remove_profile_picture = true;
        profilePicturePreview.value = null;
        if (fileInputRef.value) {
            fileInputRef.value.value = '';
        }
    }
};
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
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                        Editar Perfil
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Actualiza tu información</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="saveProfile">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Columna Izquierda: Avatar y Datos Básicos -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Tarjeta de Avatar -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
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
                                    @click="removeProfilePicture"
                                    type="button"
                                    class="mt-3 text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Eliminar foto
                                </button>
                                <p v-else class="mt-3 text-xs text-gray-400">Click para añadir foto</p>
                                
                                <h3 class="mt-4 text-xl font-bold text-gray-900">{{ form.display_name || form.name }}</h3>
                                <p class="text-sm text-gray-500">@{{ form.username || 'sin-usuario' }}</p>
                            </div>

                            <!-- Tarjeta de Ubicación -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <span class="text-xl">📍</span> Ubicación
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="location_city" value="Ciudad Base" />
                                        <CityAutocomplete v-model="form.location_city" :api-key="geoapifyKey" @city-selected="handleCitySelected" class="mt-1"/>
                                        <InputError class="mt-2" :message="form.errors.location_city" />
                                    </div>

                                    <div class="pt-2 border-t border-gray-100">
                                        <div v-if="hasLocationSaved" class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-100">
                                            <span class="text-xs font-semibold text-green-700 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                GPS Activo
                                            </span>
                                            <button type="button" @click="getUserLocation" class="text-xs text-indigo-600 font-medium hover:text-indigo-800">Actualizar</button>
                                        </div>
                                        <button v-else type="button" @click="getUserLocation" class="w-full mt-2 flex items-center justify-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-100 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Activar GPS
                                        </button>
                                        <p v-if="locationStatus" class="mt-2 text-xs text-center text-indigo-600 animate-pulse">{{ locationStatus }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Detalles y Fitness -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Información Personal -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-2">Información Personal</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <InputLabel for="name" value="Nombre Completo" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            </div>
                                            <TextInput id="name" type="text" class="mt-1 block w-full pl-10" v-model="form.name" required />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>
                                    <div>
                                        <InputLabel for="display_name" value="Nombre Público" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
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
                                        <textarea id="bio" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none" v-model="form.bio" placeholder="¿Cuáles son tus objetivos? ¿Qué te motiva?"></textarea>
                                        <InputError class="mt-2" :message="form.errors.bio" />
                                    </div>
                                </div>
                            </div>

                            <!-- Perfil Fitness -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-2">Perfil Fitness</h3>
                                
                                <div class="space-y-8">
                                    <!-- Nivel de Experiencia (Visual) -->
                                    <div>
                                        <InputLabel value="Nivel de Experiencia" class="mb-3" />
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div v-for="level in ['Principiante', 'Intermedio', 'Avanzado']" :key="level"
                                                 @click="form.experience_level = level"
                                                 class="cursor-pointer border rounded-xl p-4 text-center transition-all duration-200 relative overflow-hidden group"
                                                 :class="form.experience_level === level ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'">
                                                <div class="text-2xl mb-1">
                                                    {{ level === 'Principiante' ? '🌱' : (level === 'Intermedio' ? '⚡' : '🔥') }}
                                                </div>
                                                <span class="block font-bold text-sm" :class="form.experience_level === level ? 'text-indigo-700' : 'text-gray-700'">{{ level }}</span>
                                                <div v-if="form.experience_level === level" class="absolute top-2 right-2 text-indigo-600">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Intereses -->
                                    <div>
                                        <InputLabel value="Mis Intereses / Deportes" class="mb-2" />
                                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                            <TagsInput v-model="form.interests" :interests="allInterests" />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.interests" />
                                    </div>

                                    <!-- Buscando... -->
                                    <div>
                                        <InputLabel value="Busco compañero principalmente para..." class="mb-2" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-xl">🤝</span>
                                            </div>
                                            <SelectInput
                                                v-model="form.looking_for_interest_id"
                                                :options="interestOptions"
                                                class="w-full pl-10 h-12 text-base"
                                            />
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Esto ayudará a otros usuarios a encontrarte si buscan lo mismo.</p>
                                    </div>

                                    <!-- Disponibilidad -->
                                    <div>
                                        <InputLabel value="Disponibilidad Habitual" class="mb-2" />
                                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                            <AvailabilityInput v-model="form.availability_general" />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.availability_general" />
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex flex-col items-center justify-center gap-4 pt-6 border-t border-gray-100">
                                <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-green-600 flex items-center bg-green-50 px-4 py-2 rounded-full">
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
    </AuthenticatedLayout>
</template>