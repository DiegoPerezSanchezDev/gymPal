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
// Esta es la forma correcta de recibir los datos que el ProfileController envía a esta página.
const props = defineProps({
    user: Object,
    interests: Array,
    mustVerifyEmail: Boolean,
    status: String,
});

// --- DATOS GLOBALES ---
// Obtenemos solo los datos que son verdaderamente globales desde usePage().
const geoapifyKey = usePage().props.geoapify_key;

// --- OPCIONES PARA LOS SELECTS ---
// Usamos `props.interests` como la fuente de verdad.
const allInterests = computed(() => props.interests || []);

const experienceOptions = [
    { value: 'Principiante', label: 'Principiante' },
    { value: 'Intermedio', label: 'Intermedio' },
    { value: 'Avanzado', label: 'Avanzado' }
];

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
    // Usamos `props.user` como la fuente de verdad.
    const nameForAvatar = props.user.display_name || props.user.name || 'Gym Pal';
    return props.user.profile_picture_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(nameForAvatar)}&background=random&color=fff&size=128&font-size=0.33`;
});

// --- FORMULARIO (INICIALIZADO CON `props.user`) ---
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
});

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

const saveProfile = () => {
    form.patch(route('profile.update'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Editar Perfil" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-2 sm:px-4 py-8">
            <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8">
                <!-- Encabezado del formulario -->
                <div class="text-center mb-8">
                    <img :src="avatarUrl" :alt="form.display_name || form.name" class="w-32 h-32 rounded-full object-cover border-4 border-indigo-200 shadow-md mx-auto mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">Editar Perfil</h1>
                    <p class="mt-1 text-sm text-gray-600">Actualiza tu información para que otros puedan encontrarte.</p>
                </div>
                
                <!-- Formulario -->
                <form @submit.prevent="saveProfile" class="w-full space-y-6">
                    <!-- Sección de Información Personal -->
                    <div class="space-y-4 p-4 border rounded-md">
                        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Información Personal</h3>
                        <div>
                            <InputLabel for="name" value="Nombre Completo" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="display_name" value="Nombre a Mostrar" />
                            <TextInput id="display_name" type="text" class="mt-1 block w-full" v-model="form.display_name" autocomplete="display_name" />
                            <InputError class="mt-2" :message="form.errors.display_name" />
                        </div>
                        <div>
                            <InputLabel for="username" value="Usuario (Editable)" />
                            <TextInput id="username" type="text" class="mt-1 block w-full" v-model="form.username" required autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.username" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" type="email" class="mt-1 block w-full bg-gray-100 cursor-not-allowed" v-model="form.email" readonly />
                        </div>
                        <div>
                            <InputLabel for="bio" value="Biografía" />
                            <textarea id="bio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.bio" rows="4" placeholder="Cuéntale a otros sobre tus metas, qué te motiva..."></textarea>
                            <InputError class="mt-2" :message="form.errors.bio" />
                        </div>
                    </div>

                    <!-- Sección de Ubicación -->
                    <div class="space-y-4 p-4 border rounded-md">
                        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Ubicación</h3>
                        <div>
                            <InputLabel for="location_city" value="Ciudad" />
                            <CityAutocomplete v-model="form.location_city" :api-key="geoapifyKey" @city-selected="handleCitySelected" class="mt-1"/>
                            <InputError class="mt-2" :message="form.errors.location_city" />
                        </div>
                        <div>
                            <InputLabel value="Ubicación Precisa para Búsquedas" />
                            <p class="text-xs text-gray-500">Activa esto para aparecer en las búsquedas "cerca de mí".</p>
                            <div v-if="hasLocationSaved" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-md">
                                <div class="flex items-center justify-between">
                                    <div><p class="text-sm font-semibold text-green-800">✅ ¡Tu ubicación está guardada!</p></div>
                                    <button type="button" @click="getUserLocation" class="text-sm text-indigo-600 hover:underline">Actualizar</button>
                                </div>
                            </div>
                            <div v-else>
                                <button type="button" @click="getUserLocation" class="mt-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                    📍 Activar y usar mi ubicación actual
                                </button>
                            </div>
                            <p v-if="locationStatus" class="mt-2 text-sm text-gray-600">{{ locationStatus }}</p>
                        </div>
                    </div>

                    <!-- Sección de Fitness -->
                    <div class="space-y-6 p-4 border rounded-md">
                        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Información de Entrenamiento</h3>
                        
                        <SelectInput
                            id="experience_level"
                            label="Nivel de Experiencia"
                            v-model="form.experience_level"
                            :options="experienceOptions"
                        />
                        
                        <SelectInput
                            id="looking_for"
                            label="Busco compañero/a principalmente para..."
                            v-model="form.looking_for_interest_id"
                            :options="interestOptions"
                        />

                        <div>
                            <InputLabel value="Mis Intereses Deportivos" />
                            <TagsInput v-model="form.interests" :interests="allInterests" class="mt-2"/>
                            <InputError class="mt-2" :message="form.errors.interests" />
                        </div>
                        
                        <div>
                            <InputLabel value="Mi Disponibilidad General" />
                            <AvailabilityInput v-model="form.availability_general" class="mt-2" />
                            <InputError class="mt-2" :message="form.errors.availability_general" />
                        </div>
                    </div>

                    <!-- Botón guardar -->
                    <div class="flex items-center justify-end gap-4 pt-4">
                        <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Guardado.</p>
                        <PrimaryButton :disabled="form.processing">Guardar Cambios</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>