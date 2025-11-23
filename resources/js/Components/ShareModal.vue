<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    log: Object,
    user: Object
});

const emit = defineEmits(['close']);

const captureRef = ref(null);
const isGenerating = ref(false);

const themes = [
    { id: 'classic', bg: 'from-gray-900 via-black to-gray-800', name: 'Classic Black', icon: '⚡' },
    { id: 'royal', bg: 'from-indigo-900 via-purple-900 to-black', name: 'Royal Purple', icon: '👑' },
    { id: 'ocean', bg: 'from-blue-900 via-slate-900 to-black', name: 'Deep Ocean', icon: '🌊' },
    { id: 'forest', bg: 'from-emerald-900 via-green-900 to-black', name: 'Dark Forest', icon: '🌲' },
    { id: 'fire', bg: 'from-red-900 via-orange-900 to-black', name: 'Magma', icon: '🔥' },
];

const selectedTheme = ref(themes[0]);
const showThemeSelector = ref(false);

const getBestLift = () => {
    if (!props.log.exercises_data) return null;
    
    let maxWeight = 0;
    let bestExercise = '';
    
    props.log.exercises_data.forEach(ex => {
        ex.sets.forEach(set => {
            const weight = Number(set.weight);
            if (weight > maxWeight) {
                maxWeight = weight;
                bestExercise = ex.name;
            }
        });
    });
    
    return maxWeight > 0 ? { name: bestExercise, weight: maxWeight } : null;
};

const formatTime = (minutes) => {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
};

const bestLift = computed(() => getBestLift());

// Bloquear/desbloquear scroll cuando el modal se abre/cierra
watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}, { immediate: true });

// Publicar workout como imagen en el feed
const createPost = async () => {
    if (!captureRef.value) return;
    
    isGenerating.value = true;
    
    try {
        // Paso 1: Capturar la tarjeta como imagen usando html2canvas
        const html2canvas = (await import('html2canvas')).default;
        
        const canvas = await html2canvas(captureRef.value, {
            scale: 2,
            backgroundColor: '#000000',
            logging: false,
            useCORS: true,
            allowTaint: true
        });
        
        // Paso 2: Convertir canvas a blob
        const blob = await new Promise(resolve => {
            canvas.toBlob(resolve, 'image/png', 1.0);
        });
        
        // Paso 3: Crear FormData con la imagen y datos del post
        const formData = new FormData();
        formData.append('image', blob, `workout-${props.log.id}.png`);
        formData.append('content', `¡Entrenamiento completado! 💪\n\n${props.log.workout_name}`);
        formData.append('workout_log_id', props.log.id);
        
        // Paso 4: Enviar al backend
        await axios.post('/posts', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        // Cerrar modal
        emit('close');
        
        // Redirigir al feed
        window.location.href = '/feed';
    } catch (error) {
        console.error('Error al publicar:', error);
        alert('No se pudo publicar el entrenamiento. Inténtalo de nuevo.');
    } finally {
        isGenerating.value = false;
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" @click="$emit('close')"></div>

        <!-- Modal Content Container -->
        <div class="relative w-full max-w-md flex flex-col items-center z-10 max-h-full">
            
            <!-- Toolbar -->
            <div class="w-full flex justify-between items-center mb-2 px-4 shrink-0">
                <button 
                    @click="showThemeSelector = !showThemeSelector"
                    class="flex items-center gap-2 px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-full text-white text-xs font-bold transition backdrop-blur-sm border border-white/5"
                >
                    🎨 <span class="hidden sm:inline">Fondo</span>
                </button>
                
                <button @click="$emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition border border-white/5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Theme Selector -->
            <div v-if="showThemeSelector" class="w-full mb-4 bg-white/5 backdrop-blur-md rounded-2xl p-3 flex gap-3 overflow-x-auto shrink-0 mx-4 border border-white/10">
                <button 
                    v-for="theme in themes" 
                    :key="theme.id"
                    @click="selectedTheme = theme"
                    class="w-8 h-8 rounded-full flex-shrink-0 border-2 transition-all bg-gradient-to-br shadow-lg"
                    :class="[theme.bg, selectedTheme.id === theme.id ? 'border-white scale-110' : 'border-transparent opacity-50 hover:opacity-100']"
                    :title="theme.name"
                ></button>
            </div>

            <!-- Scrollable Area for Card (if screen is very small) -->
            <div class="flex-1 w-full flex items-center justify-center overflow-y-auto no-scrollbar py-2">
                <!-- THE CARD TO CAPTURE -->
                <div class="relative shadow-2xl rounded-3xl overflow-hidden group transform transition-transform duration-300 origin-center scale-90 sm:scale-100">
                    <div 
                        ref="captureRef"
                        class="w-[320px] aspect-[9/16] text-white p-6 flex flex-col justify-between relative bg-gradient-to-br"
                        :class="selectedTheme.bg"
                    >
                        <!-- Glow Effects -->
                        <div class="absolute top-0 right-0 w-80 h-80 bg-white rounded-full blur-[120px] opacity-[0.08] -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-80 h-80 bg-white rounded-full blur-[120px] opacity-[0.05] translate-y-1/2 -translate-x-1/2"></div>

                        <!-- Header -->
                        <div class="relative z-10 flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center font-black text-lg border border-white/10 shadow-inner">
                                    {{ selectedTheme.icon }}
                                </div>
                                <div>
                                    <div class="font-black tracking-widest text-sm">GYMPAL</div>
                                    <div class="text-[10px] text-white/50 uppercase tracking-widest font-bold">Workout Log</div>
                                </div>
                            </div>
                            <div class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-[10px] font-bold text-white/70 backdrop-blur-sm">
                                {{ new Date(log.created_at).toLocaleDateString() }}
                            </div>
                        </div>

                        <!-- Main Content (Centered) -->
                        <div class="relative z-10 flex-1 flex flex-col justify-center space-y-6 py-4">
                            <div>
                                <h1 class="text-3xl font-black leading-none mb-2 text-transparent bg-clip-text bg-gradient-to-b from-white to-white/60 drop-shadow-sm line-clamp-2">
                                    {{ log.workout_name }}
                                </h1>
                            </div>

                            <!-- Main Stats Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-3 border border-white/5 hover:bg-white/10 transition">
                                    <div class="text-2xl font-black text-white">{{ formatTime(log.duration_minutes) }}</div>
                                    <div class="text-[9px] uppercase tracking-wider text-white/40 font-bold mt-1">Tiempo Total</div>
                                </div>
                                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-3 border border-white/5 hover:bg-white/10 transition">
                                    <div class="text-2xl font-black text-white">{{ log.completed_sets }}</div>
                                    <div class="text-[9px] uppercase tracking-wider text-white/40 font-bold mt-1">Series</div>
                                </div>
                            </div>

                            <!-- Highlight Metric -->
                            <div v-if="bestLift" class="relative overflow-hidden bg-gradient-to-r from-white/10 to-white/5 backdrop-blur-md rounded-2xl p-5 border border-white/10 shadow-lg">
                                <div class="absolute top-0 right-0 p-3 opacity-20">
                                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <div class="relative z-10">
                                    <div class="text-[9px] uppercase tracking-wider text-white/60 font-bold mb-1">Mejor Levantamiento</div>
                                    <div class="text-3xl font-black text-white mb-1 tracking-tight">{{ bestLift.weight }}<span class="text-lg font-medium text-white/60 ml-1">kg</span></div>
                                    <div class="text-sm font-bold text-white/90 truncate">{{ bestLift.name }}</div>
                                </div>
                            </div>
                            
                            <div v-else class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/5">
                                 <div class="text-3xl font-black text-white">{{ log.exercises_data.length }}</div>
                                 <div class="text-[9px] uppercase tracking-wider text-white/40 font-bold mt-1">Ejercicios Completados</div>
                            </div>
                        </div>

                        <!-- Footer (Integrado) -->
                        <div class="relative z-10 mt-auto">
                            <div class="h-px w-full bg-gradient-to-r from-transparent via-white/20 to-transparent mb-4"></div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 border border-white/20 shadow-lg flex items-center justify-center">
                                        <span class="text-white text-xs font-black">{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-xs font-bold text-white leading-tight">{{ user.name }}</div>
                                        <div class="text-[9px] text-white/50 font-medium">@{{ user.username }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[9px] text-white/40 font-bold uppercase tracking-wider mb-0.5">Entrena con</div>
                                    <div class="text-xs font-black text-white tracking-wide flex items-center justify-end gap-1">
                                        GymPal <span class="text-[9px] opacity-50 font-normal">App</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Publish Button -->
            <div class="w-full px-6 pb-4 shrink-0">
                <button 
                    @click="createPost"
                    :disabled="isGenerating"
                    class="w-full py-3 bg-white text-black rounded-xl font-black text-base hover:bg-gray-100 hover:scale-[1.02] transition-all shadow-xl flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <span v-if="isGenerating" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Publicando...
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Publicar en GymPal
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
