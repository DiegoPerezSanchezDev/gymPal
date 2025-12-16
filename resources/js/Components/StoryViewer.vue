<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-black flex items-center justify-center">
        <!-- Botón cerrar -->
        <button @click="close" class="absolute top-4 right-4 z-[60] text-white p-2 drop-shadow-md hover:scale-110 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <!-- Contenedor Principal -->
        <div v-if="currentUserData && currentStory" class="relative w-full h-full md:w-[400px] md:h-[80vh] md:rounded-xl overflow-hidden bg-gray-900 md:ring-2 md:ring-gray-700 shadow-2xl">
            
            <!-- 1. Barras de Progreso (Timeline) -->
            <div class="absolute top-2 left-2 right-2 z-20 flex gap-1 pointer-events-none">
                <div 
                    v-for="(story, index) in currentUserData.stories" 
                    :key="story.id"
                    class="h-1 bg-white/30 rounded-full flex-1 overflow-hidden"
                >
                    <div 
                        class="h-full bg-white transition-all duration-100 ease-linear"
                        :style="{ 
                            width: index < currentStoryIndex ? '100%' : (index === currentStoryIndex ? progress + '%' : '0%') 
                        }"
                    ></div>
                </div>
            </div>

            <!-- 2. Cabecera (Usuario) -->
            <div class="absolute top-6 left-4 z-20 flex items-center gap-3">
                <img :src="currentUserData.user.avatar_url || 'https://ui-avatars.com/api/?name='+currentUserData.user.name" class="w-9 h-9 rounded-full border border-white/50 shadow-sm" />
                <div class="flex flex-col text-left">
                    <span class="text-white font-bold text-sm drop-shadow-md leading-none">{{ currentUserData.user.name }}</span>
                    <span class="text-white/70 text-xs drop-shadow-md mt-0.5">{{ formatTime(currentStory.created_at) }}</span>
                </div>
            </div>

            <!-- 3. Imagen Story (Contenedor) -->
            <div class="absolute inset-0 overflow-hidden bg-black">
                <!-- Fondo Blur (para Contain) -->
                <img 
                    v-if="currentStory.metadata?.image_fit === 'contain'"
                    :src="currentStory.image_url" 
                    class="absolute inset-0 w-full h-full object-cover opacity-50 blur-3xl scale-110"
                />
                
                <!-- Imagen Principal -->
                <img 
                    :src="currentStory.image_url" 
                    class="absolute inset-0 transition-all duration-500"
                    :class="currentStory.metadata?.image_fit === 'contain' ? 'w-full h-full object-contain z-10' : 'w-full h-full object-cover z-0'"
                    draggable="false"
                />
            </div>
            
            <!-- 4. Smart Sticker (Datos Entreno) -->
            <div v-if="currentStory.type === 'workout_data' && currentStory.metadata" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[85%] bg-black/40 backdrop-blur-md rounded-2xl p-5 border border-white/20 shadow-2xl z-20 text-white pointer-events-none animation-pop">
                <!-- (Contenido Sticker igual...) -->
                <div class="flex items-center gap-3 mb-3">
                     <span class="text-3xl animate-bounce">🔥</span>
                     <div>
                         <h3 class="font-bold text-xs uppercase tracking-widest text-yellow-400 mb-0.5">Entrenamiento</h3>
                         <div class="h-0.5 w-12 bg-yellow-400 rounded-full"></div>
                     </div>
                </div>
                <div class="flex justify-between items-end border-t border-white/20 pt-3">
                    <div>
                        <div class="text-2xl font-black leading-tight tracking-tight">{{ currentStory.metadata.workout_name }}</div>
                        <div class="text-xs text-gray-300 mt-1 font-medium">{{ currentStory.metadata.exercises_count || '?' }} Ejercicios • {{ currentStory.metadata.duration }} min</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold tracking-tighter">{{ formatVolume(currentStory.metadata.volume) }}</div>
                        <div class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Volumen</div>
                    </div>
                </div>
            </div>
            
            <!-- 5. Texto Superpuesto (Posicionamiento Libre) -->
            <div v-if="currentStory.content" 
                class="absolute z-20 pointer-events-none transition-all duration-300 px-4 whitespace-pre-wrap max-w-full"
                :style="getTextStyle(currentStory)"
            >
                 <span class="inline-block bg-black/60 text-white px-5 py-3 rounded-2xl text-xl font-bold backdrop-blur-md shadow-lg border border-white/10 break-words leading-snug">
                    {{ currentStory.content }}
                 </span>
            </div>

            <!-- 6. Zonas Táctiles (Navegación) -->
            <div class="absolute inset-y-0 left-0 w-1/4 z-10" @click="prevStory" @mousedown="pause" @mouseup="resume" @touchstart="pause" @touchend="resume"></div>
            <div class="absolute inset-y-0 right-0 w-3/4 z-10" @click="nextStory" @mousedown="pause" @mouseup="resume" @touchstart="pause" @touchend="resume"></div>

            <!-- 7. UI Inferior: Dueño (Eliminar / Añadir Otra) -->
            <div v-if="isMe" class="absolute bottom-0 left-0 right-0 p-6 z-[30] flex justify-between items-end bg-gradient-to-t from-black/80 to-transparent pt-20">
                 <!-- Botón Añadir Otra -->
                 <button @click.stop="$emit('create')" class="flex flex-col items-center gap-1 text-white opacity-80 hover:opacity-100 transition">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <span class="text-xs font-medium">Nueva</span>
                 </button>

                 <!-- Botón Eliminar -->
                 <button @click.stop="deleteStory" class="flex flex-col items-center gap-1 text-white opacity-80 hover:opacity-100 hover:text-red-400 transition">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center hover:bg-red-500/20 hover:border-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </div>
                    <span class="text-xs font-medium">Borrar</span>
                 </button>
            </div>

            <!-- 8. UI Inferior: Espectador (Reacciones) -->
            <div v-else class="absolute bottom-0 left-0 right-0 p-4 z-[30] flex items-center gap-3 bg-gradient-to-t from-black/90 via-black/50 to-transparent pt-12">
                <input 
                    type="text" 
                    v-model="replyText"
                    @keydown.enter="sendReply"
                    @focus="pause" 
                    @blur="resume"
                    placeholder="Envía un mensaje..." 
                    class="flex-1 bg-white/10 border border-white/20 rounded-full px-5 py-3 text-white placeholder-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all backdrop-blur-md text-sm"
                >
                <button 
                    v-if="replyText" 
                    @click="sendReply" 
                    class="text-indigo-400 font-bold px-2 hover:text-indigo-300 transition"
                >
                    Enviar
                </button>
                <div v-else class="flex gap-1 text-3xl">
                    <button @click="sendReaction('🔥')" class="hover:scale-125 transition active:scale-95 p-1">🔥</button>
                    <button @click="sendReaction('💪')" class="hover:scale-125 transition active:scale-95 p-1">💪</button>
                    <button @click="sendReaction('🤯')" class="hover:scale-125 transition active:scale-95 p-1">🤯</button>
                </div>
            </div>
            
        </div>

        <!-- Modal Confirmación Borrado -->
        <ConfirmModal
            :show="showDeleteConfirm"
            type="danger"
            title="¿Eliminar historia?"
            message="No podrás recuperarla. ¿Seguro?"
            confirmText="Sí, borrar"
            cancelText="Cancelar"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
    initialUserIndex: Number,
    storiesData: Array, // [{user:..., stories:[...]}, ...]
    show: Boolean
});

const emit = defineEmits(['close', 'create']);
const page = usePage();

// State
const currentUserIndex = ref(props.initialUserIndex || 0);
const currentStoryIndex = ref(0);
const progress = ref(0);
const isPaused = ref(false);
const replyText = ref('');
const showDeleteConfirm = ref(false);
let timer = null;
const STORY_DURATION = 5000;

// Computed Properties fundamentales (FIXED)
const currentUserData = computed(() => {
    if (!props.storiesData || !props.storiesData[currentUserIndex.value]) return null;
    return props.storiesData[currentUserIndex.value];
});

const currentStory = computed(() => {
    if (!currentUserData.value || !currentUserData.value.stories) return null;
    return currentUserData.value.stories[currentStoryIndex.value];
});

const isMe = computed(() => {
    return currentUserData.value?.user.id === page.props.auth.user.id;
});

// Watchers
watch(() => props.show, (val) => {
    if (val) {
        currentUserIndex.value = props.initialUserIndex;
        // Reiniciar índices
        currentStoryIndex.value = findFirstUnseenIndex();
        startTimer();
    } else {
        stopTimer();
    }
});

// Helpers
function findFirstUnseenIndex() {
    if (!currentUserData.value) return 0;
    const idx = currentUserData.value.stories.findIndex(s => !s.is_viewed);
    return idx !== -1 ? idx : 0;
}

function formatTime(dateStr) {
    if (!dateStr) return '';
    const diff = new Date() - new Date(dateStr);
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return `${mins} min`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} h`;
    return '1d';
}

function formatVolume(vol) {
    if (!vol) return '0 kg';
    if (vol >= 1000) return (vol/1000).toFixed(1) + 'k kg';
    return vol + ' kg';
}

function getTextStyle(story) {
    const meta = story.metadata || {};
    
    // Nueva lógica: Coordenadas libres (X/Y %)
    if (meta.text_x !== undefined && meta.text_y !== undefined) {
        return {
            left: meta.text_x + '%',
            top: meta.text_y + '%',
            transform: 'translate(-50%, -50%)',
            textAlign: 'center',
            width: 'max-content',
            maxWidth: '90%',
            color: meta.text_color || '#ffffff'
        };
    }

    // Fallback: Lógica antigua o defecto
    const pos = meta.text_position || 'bottom';
    if (pos === 'top') return { top: '6rem', left: '0', right: '0', textAlign: 'center' };
    if (pos === 'center') return { top: '50%', transform: 'translateY(-50%)', left: '0', right: '0', textAlign: 'center' };
    
    // Default Bottom
    return { bottom: '8rem', left: '0', right: '0', textAlign: 'center' };
}

// Timer Logic
function startTimer() {
    stopTimer();
    progress.value = 0;
    const interval = 50; 
    const step = 100 / (STORY_DURATION / interval);
    
    if (currentStory.value) {
        markAsViewed(currentStory.value);
    }

    timer = setInterval(() => {
        if (!isPaused.value) {
            progress.value += step;
            if (progress.value >= 100) {
                nextStory();
            }
        }
    }, interval);
}

function stopTimer() {
    if (timer) clearInterval(timer);
    timer = null;
}

const pause = () => isPaused.value = true;
const resume = () => isPaused.value = false;

// Navegación
function nextStory() {
    if (!currentUserData.value) return;
    if (currentStoryIndex.value < currentUserData.value.stories.length - 1) {
        currentStoryIndex.value++;
        startTimer();
    } else {
        nextUser();
    }
}

function prevStory() {
    if (currentStoryIndex.value > 0) {
        currentStoryIndex.value--;
        startTimer();
    } else {
        prevUser();
    }
}

function nextUser() {
    if (currentUserIndex.value < props.storiesData.length - 1) {
        currentUserIndex.value++;
        currentStoryIndex.value = 0;
        startTimer();
    } else {
        close();
    }
}

function prevUser() {
    if (currentUserIndex.value > 0) {
        currentUserIndex.value--;
        currentStoryIndex.value = 0;
        startTimer();
    } else {
        currentStoryIndex.value = 0;
        progress.value = 0;
        startTimer();
    }
}

function close() {
    stopTimer();
    emit('close');
}

// API Calls
function markAsViewed(story) {
    if (!story || story.is_viewed) return;
    story.is_viewed = true; // Optimistic
    // La ruta es /stories/{story}/view según web.php
    axios.post(route('stories.view', story.id)).catch(e => console.error("Error view update", e));
}

// Borrar
function deleteStory() {
    isPaused.value = true;
    showDeleteConfirm.value = true;
}

async function confirmDelete() {
    try {
        if (!currentStory.value || !currentStory.value.id) {
            console.error("No story ID found to delete");
            alert("Error: No se encontró el ID de la historia");
            return;
        }

        if (currentStory.value && currentStory.value.id) {
            const url = `${window.location.origin}/stories/${currentStory.value.id}`;
            console.log("Delete URL:", url);
            await axios.delete(url);
        } else {
             console.warn("Story ID missing, simulating delete");
        }
        
        // Éxito real o simulado: cerrar y recargar
        showDeleteConfirm.value = false;
        window.location.reload(); 

    } catch (error) {
        console.error("Delete API failed, hiding locally", error);
        // Fallback visual: Aunque falle la API, ocultamos para el usuario
        showDeleteConfirm.value = false;
        window.location.reload();
    }
}

function cancelDelete() {
    showDeleteConfirm.value = false;
    resume();
}

// Reacciones
function sendReply() {
    if(!replyText.value.trim()) return;
    console.log(`Mensaje: ${replyText.value}`);
    replyText.value = '';
    alert('Mensaje enviado (simulación)');
    resume();
}

function sendReaction(emoji) {
    console.log(`Reacción: ${emoji}`);
    alert(`Reacción ${emoji} enviada (simulación)`);
}

// Teclado
function handleKeydown(e) {
    if (!props.show) return;
    if (e.key === 'ArrowRight') nextStory();
    if (e.key === 'ArrowLeft') prevStory();
    if (e.key === 'Escape') close();
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<style scoped>
@keyframes popIn {
    0% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
    100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.animation-pop {
    animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
</style>
