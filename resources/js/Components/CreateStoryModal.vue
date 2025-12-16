<template>
    <Modal :show="show" @close="closeModal" maxWidth="2xl">
        <div class="bg-black text-white h-[90vh] md:h-[85vh] flex flex-col items-center justify-center overflow-hidden relative select-none">
            
            <!-- 1. PANTALLA DE CARGA (Empty State) -->
            <div v-if="!previewUrl" class="w-full h-full flex flex-col items-center justify-center p-10 cursor-pointer hover:bg-white/5 transition" @click="$refs.fileInput.click()">
                 <div class="w-24 h-24 bg-gradient-to-tr from-yellow-400 via-red-500 to-purple-600 rounded-full flex items-center justify-center mb-6 shadow-2xl animate-pulse">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                 </div>
                 <h3 class="text-3xl font-bold">Crear Historia</h3>
                 <p class="text-gray-400 mt-2">Comparte tu progreso hoy</p>
                 <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onFileChange">
            </div>

            <!-- 2. EDITOR PRINCIPAL -->
            <div v-else class="relative w-full h-full flex items-center justify-center bg-gray-900">
                
                <!-- HEADER (Controles Superiores) -->
                <div class="absolute top-0 left-0 right-0 z-50 p-4 flex justify-between items-start bg-gradient-to-b from-black/60 to-transparent pointer-events-none">
                    <button @click="closeModal" class="pointer-events-auto p-2 hover:bg-white/20 rounded-full transition">
                        <svg class="w-7 h-7 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <div class="flex gap-4 pointer-events-auto">
                        <!-- Botón Texto -->
                        <button @click="startEditingText" class="p-2 hover:bg-white/20 rounded-full transition" title="Añadir Texto">
                            <span class="font-bold text-xl drop-shadow-md border-2 border-white w-8 h-8 rounded-full flex items-center justify-center text-sm">Aa</span>
                        </button>
                        
                        <!-- Botón Sticker Entreno -->
                        <button v-if="recentActivity" @click="toggleSticker" class="p-2 hover:bg-white/20 rounded-full transition" :class="{'text-yellow-400': useSmartSticker}">
                            <svg class="w-7 h-7 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </button>

                        <!-- Botón Fit -->
                        <button @click="toggleFit" class="p-2 hover:bg-white/20 rounded-full transition">
                             <svg v-if="imageFit === 'cover'" class="w-7 h-7 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                             <svg v-else class="w-7 h-7 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3v3a2 2 0 01-2 2H3m18 0h-3a2 2 0 01-2-2V3m0 18v-3a2 2 0 012-2h3M3 16h3a2 2 0 012 2v3" /></svg>
                        </button>
                    </div>
                </div>

                <!-- CANVAS DE IMAGEN (Área de Trabajo) -->
                <div 
                    ref="canvasRef"
                    class="relative w-full h-full overflow-hidden flex items-center justify-center bg-black"
                >
                    <!-- Capa Background Blur -->
                    <img 
                        v-if="imageFit === 'contain'"
                        :src="previewUrl" 
                        class="absolute inset-0 w-full h-full object-cover opacity-60 blur-3xl scale-125" 
                    />

                    <!-- Imagen Real -->
                    <img 
                        :src="previewUrl" 
                        class="relative z-0 shadow-2xl transition-all duration-300 pointer-events-none"
                        :class="imageFit === 'cover' ? 'w-full h-full object-cover' : 'max-w-full max-h-full object-contain'"
                    />

                    <!-- CAPA INTERACTIVA (Elementos Draggables) -->
                    <div class="absolute inset-0 z-10 overflow-hidden" 
                        @mousedown="startDrag" @touchstart="startDrag"
                        @mousemove="doDrag" @touchmove="doDrag"
                        @mouseup="stopDrag" @touchend="stopDrag"
                        @mouseleave="stopDrag"
                    >
                        <!-- ELEMENTO: Smart Sticker -->
                        <div v-if="useSmartSticker && recentActivity" 
                             class="absolute w-[80%] max-w-sm"
                             :style="{ left: '50%', top: '50%', transform: 'translate(-50%, -50%)' }"
                        >
                            <div class="bg-black/40 backdrop-blur-md rounded-2xl p-5 border border-white/20 shadow-2xl text-white pointer-events-none">
                                <div class="flex items-center gap-3 mb-2">
                                     <span class="text-3xl">🔥</span>
                                     <div>
                                         <h3 class="font-bold text-xs uppercase tracking-widest text-yellow-400">Entrenamiento</h3>
                                     </div>
                                </div>
                                <div class="flex justify-between items-end border-t border-white/20 pt-2">
                                    <div>
                                        <div class="text-xl font-black">{{ recentActivity.name }}</div>
                                        <div class="text-xs text-gray-300 mt-1">{{ recentActivity.sets }} Series • {{ recentActivity.duration }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold">{{ formatVolume(recentActivity.volume) }}</div>
                                        <div class="text-[10px] uppercase text-gray-400">Volumen</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ELEMENTO: Texto Usuario -->
                        <div 
                            v-if="form.content && !isEditingText"
                            class="absolute cursor-grab active:cursor-grabbing px-4"
                            :style="{ 
                                left: textCoords.x + '%', 
                                top: textCoords.y + '%',
                                transform: 'translate(-50%, -50%)' 
                            }"
                            @mousedown.stop="startDragText"
                            @touchstart.stop="startDragText"
                            @click.stop="startEditingText"
                        >
                             <span 
                                class="inline-block px-4 py-2 rounded-lg text-2xl font-bold shadow-lg break-words text-center"
                                :style="{ color: textColor, backgroundColor: textBgColor }"
                             >
                                {{ form.content }}
                             </span>
                        </div>
                    </div>
                </div>

                <!-- OVERLAY: EDITOR DE TEXTO (Aparece al pulsar Aa) -->
                <div v-if="isEditingText" class="absolute inset-0 z-[60] bg-black/70 backdrop-blur-sm flex flex-col items-center justify-center">
                    <!-- Input Transparente Gigante -->
                    <textarea 
                        v-model="form.content"
                        ref="textareaRef"
                        class="bg-transparent border-none text-center text-3xl font-bold w-full max-w-md focus:ring-0 resize-none overflow-hidden placeholder-gray-500"
                        :style="{ color: textColor }"
                        placeholder="Escribe algo..."
                        rows="3"
                        @blur="finishEditing"
                        autofocus
                    ></textarea>

                    <!-- Paleta de Colores -->
                    <div class="flex gap-4 mt-8">
                        <button v-for="color in colors" :key="color" 
                            @click="textColor = color"
                            class="w-8 h-8 rounded-full border-2 border-white shadow-lg transition transform hover:scale-110"
                            :style="{ backgroundColor: color }"
                        ></button>
                    </div>

                    <button @click="finishEditing" class="mt-8 px-8 py-2 bg-white text-black rounded-full font-bold text-sm">Listo</button>
                </div>

                <!-- FOOTER: Botón Enviar (Solo si no editamos texto) -->
                <div v-if="!isEditingText" class="absolute bottom-6 right-6 z-50">
                    <button @click="submit" :disabled="form.processing" class="flex items-center gap-2 bg-white text-black pl-5 pr-2 py-3 rounded-full font-bold shadow-xl hover:scale-105 transition disabled:opacity-50">
                        <span>Tu historia</span>
                        <div class="w-8 h-8 bg-black text-white rounded-full flex items-center justify-center">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4V2A10 10 0 002 12h2a8 8 0 018-8z" /></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </button>
                </div>

            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({ show: Boolean });
const emit = defineEmits(['close']);

const form = useForm({
    image: null,
    content: '',
    workout_log_id: null,
    type: 'standard',
    metadata: {}
});

// State UI
const fileInput = ref(null);
const previewUrl = ref(null);
const recentActivity = ref(null);
const useSmartSticker = ref(false);
const imageFit = ref('cover'); // 'cover' or 'contain'

// Text Editor State
const isEditingText = ref(false);
const textColor = ref('#ffffff');
const textBgColor = ref('rgba(0,0,0,0.5)'); // Semi-transparent black default
const colors = ['#ffffff', '#000000', '#FF3B30', '#FFCC00', '#34C759', '#007AFF', '#AF52DE'];
const textareaRef = ref(null);

// Draggable State
const canvasRef = ref(null);
const textCoords = ref({ x: 50, y: 50 }); // Center default
const isDragging = ref(false);

function onFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        previewUrl.value = URL.createObjectURL(file);
        checkSmartStory();
    }
}

async function checkSmartStory() {
    try {
        const res = await axios.get(route('stories.check-activity'));
        if (res.data.has_activity) {
            recentActivity.value = res.data.workout_log;
        }
    } catch (e) { console.error(e); }
}

// Text Editor Logic
function startEditingText() {
    isEditingText.value = true;
    nextTick(() => {
        if (textareaRef.value) textareaRef.value.focus();
    });
}

function finishEditing() {
    if (!form.content.trim()) form.content = '';
    isEditingText.value = false;
}

// Draggable Logic
function startDragText(e) {
    if (!form.content) return;
    isDragging.value = true;
}

function doDrag(e) {
    if (!isDragging.value || !canvasRef.value) return;
    e.preventDefault(); // Prevent scroll on touch

    const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
    const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;

    const rect = canvasRef.value.getBoundingClientRect();
    
    let x = ((clientX - rect.left) / rect.width) * 100;
    let y = ((clientY - rect.top) / rect.height) * 100;

    // Bounds check
    x = Math.max(0, Math.min(100, x));
    y = Math.max(0, Math.min(100, y));

    textCoords.value = { x, y };
}

function stopDrag() {
    isDragging.value = false;
}

// Actions
function toggleFit() { imageFit.value = imageFit.value === 'cover' ? 'contain' : 'cover'; }
function toggleSticker() { useSmartSticker.value = !useSmartSticker.value; }
function formatVolume(vol) { return vol >= 1000 ? (vol/1000).toFixed(1) + 'k' : vol; }

function closeModal() {
    emit('close');
    setTimeout(clearImage, 300);
}

function clearImage() {
    form.reset();
    form.image = null;
    previewUrl.value = null;
    useSmartSticker.value = false;
    isEditingText.value = false;
    textCoords.value = { x: 50, y: 50 };
    if (fileInput.value) fileInput.value.value = null;
}

function submit() {
    if (useSmartSticker.value && recentActivity.value) {
        form.workout_log_id = recentActivity.value.id;
        form.type = 'workout_data';
    }

    // Save style and position
    form.metadata = { 
        text_x: textCoords.value.x,
        text_y: textCoords.value.y,
        image_fit: imageFit.value,
        text_color: textColor.value
    };

    form.post(route('stories.store'), {
        onSuccess: () => closeModal()
    });
}
</script>
