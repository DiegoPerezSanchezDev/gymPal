<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TagsInput from '@/Components/TagsInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CityAutocomplete from '@/Components/CityAutocomplete.vue';
import { useToast } from '@/composables/useToast';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import axios from 'axios';
import ToastContainer from '@/Components/ToastContainer.vue';

const props = defineProps({
    interests: Array,
    geoapify_key: String,
    user: Object,
});

const steps = ['Básicos', 'Objetivos', 'Intereses', 'Gimnasio'];
const currentStep = ref(0);
const geoapifyKey = props.geoapify_key;
const { error: showError } = useToast();

const form = useForm({
    image: null,
    display_name: props.user.name || '',
    bio: '',
    experience_level: 'Principiante',
    interests: [],
    looking_for_interest_id: null,
    location_city: '',
    latitude: null,
    longitude: null,
    gym_id: null,
});

const profilePicturePreview = ref(null);
const fileInputRef = ref(null);
const gymSearchQuery = ref('');
const gymSearchResults = ref([]);
const gymOptions = computed(() => {
    return gymSearchResults.value.map(gym => ({
        value: gym.id,
        label: gym.name,
        description: gym.address
    }));
});
const isSearchingGyms = ref(false);
const selectedGym = ref(null);
const showSkipModal = ref(false);

// --- HELPER COMPUTEDS & FUNCTIONS ---

const interestOptions = computed(() => {
    return props.interests.map(i => ({ value: i.id, label: i.name }));
});

const profilePictureUrl = computed(() => {
    if (profilePicturePreview.value) return profilePicturePreview.value;
    if (props.user.profile_picture_url) {
        // Handle Google URLs which are absolute
        if (props.user.profile_picture_url.startsWith('http')) {
            return props.user.profile_picture_url;
        }
        return `/storage/${props.user.profile_picture_url}`;
    }
    const name = form.display_name || 'User';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&color=fff&size=256`;
});

function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showError('La imagen no puede superar los 5MB');
            return;
        }
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => profilePicturePreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
}

function handleCitySelected(cityData) {
    form.location_city = cityData.name;
    form.latitude = cityData.lat; // Geoapify returns lat/lon
    form.longitude = cityData.lon;
}

// Search Gyms Logic (Simplified for now - assumes an API endpoint or internal search exists)
// Since we don't have a direct "search gym" public API route setup in the thought process yet, 
// let's assume we might need to add one or use the `discover.gyms` if it supports search.
// For MVP, if no route exists, we might need to mock it or verify `DiscoverController`.
// Checking routes... `Route::get('/discover/gyms', [DiscoverController::class, 'nearbyGyms'])` exists.
// We can use that if we send lat/lon.

// Search Gyms Logic

async function searchGyms() {
    // If no text query AND no location, do nothing
    if (!gymSearchQuery.value && !form.latitude) return;
    
    isSearchingGyms.value = true;
    try {
        const params = {
            allow_empty: 1 // Custom flag to just return some gyms if no nearby logic
        };
        
        if (form.latitude && form.longitude) {
            params.lat = form.latitude;
            params.lng = form.longitude;
        }

        // Add City Fallback
        if (form.location_city) {
            params.city = form.location_city;
        }

        // If user typed something, add it to params
        if (gymSearchQuery.value) {
            params.search = gymSearchQuery.value;
        }

        const response = await axios.get(route('discover.gyms'), { params });
        gymSearchResults.value = response.data.gyms || []; 
    } catch (e) {
        console.error(e);
    } finally {
        isSearchingGyms.value = false;
    }
}

function selectGym(gym) {
    selectedGym.value = gym;
    form.gym_id = gym.id;
    // Do NOT clear results or update query, so the list remains available if deselected
}

function nextStep() {
    // Validación: La ciudad es obligatoria en el primer paso para poder buscar gimnasios después
    if (currentStep.value === 0 && !form.location_city) {
        showError('Por favor, selecciona una ciudad para continuar. Es necesario para encontrar gimnasios y compañeros cerca de ti.');
        return;
    }

    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    } else {
        submit();
    }
}

function prevStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}

function submit() {
    form.post(route('onboarding.store'), {
        preserveScroll: true,
        onError: () => {
            // If error, maybe go back to relevant step?
            showError('Por favor revisa los campos requeridos.');
        }
    });
}

function skipOnboarding() {
    showSkipModal.value = true;
}

function confirmSkip() {
    form.post(route('onboarding.skip'));
}

// Watch for step changes to auto-load gyms
watch(currentStep, (newStep) => {
    if (newStep === 3 && form.latitude && form.longitude) {
        console.log('Auto-searching gyms...');
        searchGyms();
    }
});
</script>

<template>
    <Head title="Bienvenido a GymPal" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
        
        <!-- Navbar minimalista con botón de salir/saltar -->
        <div class="absolute top-4 right-4 sm:top-8 sm:right-8 z-10">
            <button @click="skipOnboarding" class="flex items-center gap-1 text-xs sm:text-sm font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors bg-white/50 dark:bg-black/20 backdrop-blur-sm px-3 py-1.5 rounded-full border border-transparent hover:border-gray-200 dark:hover:border-gray-700">
                Saltar por ahora <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
        
        <!-- Progress Bar -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8">
            <div class="flex justify-between mb-2">
                <span v-for="(step, index) in steps" :key="index" 
                      class="text-xs font-bold uppercase tracking-wider"
                      :class="index <= currentStep ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-600'">
                    {{ step }}
                </span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 transition-all duration-500 ease-out"
                     :style="{ width: `${((currentStep + 1) / steps.length) * 100}%` }"></div>
            </div>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl sm:rounded-3xl sm:px-10 border border-gray-100 dark:border-gray-700 relative">
                
                <!-- Background Decoration Container (Clipped) -->
                <div class="absolute inset-0 overflow-hidden sm:rounded-3xl pointer-events-none">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-32 h-32 rounded-full bg-indigo-50 dark:bg-indigo-900/20 blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 rounded-full bg-purple-50 dark:bg-purple-900/20 blur-2xl"></div>
                </div>

                <form @submit.prevent="submit">
                    
                    <!-- STEP 1: BASIC INFO -->
                    <div v-show="currentStep === 0" class="space-y-6 animate-fade-in">
                        <div class="text-center">
                            <h2 class="text-3xl font-black text-gray-900 dark:text-white">¡Hola, {{ props.user.name }}! 👋</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Vamos a configurar tu perfil para que destaques.</p>
                        </div>

                        <!-- Profile Photo -->
                        <div class="flex flex-col items-center">
                            <div class="relative group cursor-pointer" @click="fileInputRef.click()">
                                <img :src="profilePictureUrl" class="w-28 h-28 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-lg group-hover:opacity-75 transition-all">
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                            </div>
                            <input ref="fileInputRef" type="file" class="hidden" accept="image/*" @change="handleFileChange">
                            <p class="mt-2 text-xs text-gray-400">Toca para cambiar</p>
                        </div>

                        <div>
                            <InputLabel for="display_name" value="Nombre Público" />
                            <TextInput id="display_name" type="text" class="mt-1 block w-full rounded-xl" v-model="form.display_name" required autofocus />
                            <InputError class="mt-2" :message="form.errors.display_name" />
                        </div>

                         <div>
                            <InputLabel for="location" value="¿Dónde entrenas? (Ciudad)" />
                            <CityAutocomplete v-model="form.location_city" :api-key="geoapifyKey" @city-selected="handleCitySelected" class="mt-1"/>
                             <p class="text-xs text-gray-500 mt-1">Te mostraremos gimnasios y compañeros cerca de ti.</p>
                            <InputError class="mt-2" :message="form.errors.location_city" />
                        </div>
                    </div>

                    <!-- STEP 2: BIO & GOALS -->
                    <div v-show="currentStep === 1" class="space-y-6 animate-fade-in">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tus Objetivos 🎯</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Cuéntanos qué quieres lograr.</p>
                        </div>

                         <div>
                            <InputLabel for="bio" value="Sobre ti (Bio)" />
                            <textarea id="bio" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none" 
                                      v-model="form.bio" placeholder="Me gusta el powerlifting y busco mejorar mi PR de sentadilla..."></textarea>
                            <InputError class="mt-2" :message="form.errors.bio" />
                        </div>

                        <div>
                            <InputLabel value="Nivel de Experiencia" class="mb-3" />
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div v-for="level in ['Principiante', 'Intermedio', 'Avanzado']" :key="level"
                                        @click="form.experience_level = level"
                                        class="cursor-pointer border rounded-xl p-4 text-center transition-all duration-200 relative overflow-hidden group"
                                        :class="{
                                            'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-500': form.experience_level === level,
                                            'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600': form.experience_level !== level
                                        }">
                                    <div class="text-2xl mb-1">{{ level === 'Principiante' ? '🌱' : (level === 'Intermedio' ? '⚡' : '🔥') }}</div>
                                    <span class="block font-bold text-sm text-gray-900 dark:text-white">{{ level }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: INTERESTS -->
                    <div v-show="currentStep === 2" class="space-y-6 animate-fade-in">
                         <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Lo que te mueve 🔥</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Selecciona tus deportes favoritos.</p>
                        </div>

                        <div>
                            <TagsInput v-model="form.interests" :interests="props.interests" placeholder="Buscar deporte..." />
                            <InputError class="mt-2" :message="form.errors.interests" />
                        </div>

                         <div>
                            <InputLabel value="Busco compañero principalmente para..." class="mb-2" />
                            <div class="flex overflow-x-auto gap-2 pb-2 scrollbar-hide mask-fade-sides">
                                <button 
                                    type="button"
                                    @click="form.looking_for_interest_id = null"
                                    class="whitespace-nowrap px-4 py-3 rounded-xl border-2 transition-all active:scale-95 flex-shrink-0 flex items-center gap-2"
                                    :class="form.looking_for_interest_id === null 
                                        ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 text-indigo-700 dark:text-indigo-300 shadow-sm' 
                                        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-indigo-300 dark:hover:border-indigo-700'"
                                >
                                    <span class="text-lg">🌍</span>
                                    <span class="font-bold text-sm">Cualquier actividad</span>
                                    <svg v-if="form.looking_for_interest_id === null" class="w-4 h-4 ml-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>

                                <button 
                                    v-for="option in interestOptions" 
                                    :key="option.value" 
                                    type="button"
                                    @click="form.looking_for_interest_id = option.value"
                                    class="whitespace-nowrap px-4 py-3 rounded-xl border-2 transition-all active:scale-95 flex-shrink-0 flex items-center gap-2"
                                    :class="form.looking_for_interest_id === option.value 
                                        ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 text-indigo-700 dark:text-indigo-300 shadow-sm' 
                                        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-indigo-300 dark:hover:border-indigo-700'"
                                >
                                    <span class="font-bold text-sm">{{ option.label }}</span>
                                    <svg v-if="form.looking_for_interest_id === option.value" class="w-4 h-4 ml-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: GYM SELECTION -->
                    <div v-show="currentStep === 3" class="space-y-6 animate-fade-in">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tu Centro de Entrenamiento 🏟️</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gym, Box, Estudio, Parque... ¿Dónde entrenas?</p>
                        </div>

                        <div v-if="!form.location_city" class="text-center p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                             <p class="text-yellow-700 dark:text-yellow-400 text-sm">⚠️ Indica tu ciudad en el paso 1 para ver gimnasios cercanos.</p>
                             <button type="button" @click="currentStep = 0" class="mt-2 text-indigo-600 font-bold text-sm hover:underline">Ir al paso 1</button>
                        </div>

                        <div v-else>
                            <!-- Auto-loading State -->
                            <div v-if="isSearchingGyms" class="py-8 text-center bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                <svg class="animate-spin h-8 w-8 text-indigo-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">Cargando centros en {{ form.location_city }}...</p>
                            </div>

                            <!-- Selected Choice Display (Shows ONLY if selected) -->
                            <div v-else-if="selectedGym" class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800 flex items-center gap-4 animate-fade-in">
                                <div class="h-12 w-12 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-2xl shadow-sm">✅</div>
                                <div>
                                    <p class="text-xs uppercase font-bold text-indigo-500 tracking-wider">Tu elección</p>
                                    <p class="font-bold text-gray-900 dark:text-white">{{ selectedGym.name }}</p>
                                    <p class="text-xs text-gray-500">{{ selectedGym.address }}</p>
                                </div>
                                <button type="button" @click="selectedGym = null; form.gym_id = null;" class="ml-auto text-gray-400 hover:text-red-500 p-2">✕</button>
                            </div>

                            <!-- Selector UI (Hidden if selected) -->
                            <div v-else class="space-y-4">
                                <SelectInput
                                    v-model="form.gym_id"
                                    :options="gymOptions"
                                    :label="null"
                                    id="gym-selector"
                                    @update:modelValue="(val) => {
                                        const gym = gymSearchResults.find(g => g.id === val);
                                        selectGym(gym);
                                    }"
                                />
                                
                                <p v-if="gymSearchResults.length === 0" class="text-xs text-center text-gray-500 mt-2">
                                    No encontramos centros cercanos. <button type="button" @click="searchGyms" class="text-indigo-500 underline">Reintentar</button>
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="prevStep" 
                                class="flex items-center gap-2 px-6 py-3 rounded-xl text-gray-500 dark:text-gray-400 font-bold hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                                :class="{ 'opacity-0 pointer-events-none': currentStep === 0 }">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            Atrás
                        </button>

                        <button 
                            type="button" 
                            @click="nextStep" 
                            :disabled="form.processing"
                            class="group relative px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <span>{{ currentStep === steps.length - 1 ? '¡Empezar!' : 'Siguiente' }}</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            
                            <!-- Shine effect -->
                            <div class="absolute inset-0 rounded-2xl ring-1 ring-white/20 group-hover:ring-white/40 transition-all"></div>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <ConfirmModal
        :show="showSkipModal"
        title="¿Saltar configuración?"
        message="Te recomendamos completar tu perfil ahora para una mejor experiencia y para que otros GymPals puedan encontrarte. Si saltas ahora, este aviso volverá a aparecer la próxima vez que inicies sesión hasta que completes tu perfil."
        confirm-text="Saltar por ahora"
        cancel-text="Continuar configurando"
        type="warning"
        @confirm="confirmSkip"
        @cancel="showSkipModal = false"
    />

    <!-- Toasts Notifications -->
    <ToastContainer />
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
