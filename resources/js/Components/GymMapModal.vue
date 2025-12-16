<template>
    <teleport to="body">
        <transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <!-- Overlay -->
                    <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" @click="$emit('close')"></div>

                    <!-- Modal -->
                    <div class="relative inline-block w-full max-w-5xl overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between z-10 relative bg-white dark:bg-gray-800">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="text-2xl">🗺️</span>
                                Buscar Centros de Entrenamiento
                            </h3>
                            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Map Container -->
                        <div class="relative h-[600px] w-full bg-gray-100 dark:bg-gray-900">
                            <l-map
                                v-if="ready"
                                ref="map"
                                v-model:zoom="zoom"
                                :center="center"
                                :use-global-leaflet="false"
                                class="h-full w-full z-0"
                            >
                                <l-tile-layer
                                    url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                                    layer-type="base"
                                    name="OpenStreetMap"
                                ></l-tile-layer>

                                <!-- Tu Ubicación -->
                                <l-marker v-if="userLat && userLon" :lat-lng="[userLat, userLon]">
                                    <l-icon class-name="custom-me-icon" :icon-anchor="[10, 10]">
                                        <div class="w-5 h-5 bg-indigo-600 rounded-full border-2 border-white shadow-xl pulse-ring relative z-50"></div>
                                    </l-icon>
                                    <l-popup :options="{ closeButton: false, offset: [0, -5], className: 'me-popup' }">
                                        <div class="text-center font-bold text-xs px-2 py-1">📍 Tú estás aquí</div>
                                    </l-popup>
                                </l-marker>

                                <!-- Gimnasios -->
                                <l-marker 
                                    v-for="gym in gyms" 
                                    :key="gym.id" 
                                    :lat-lng="[parseFloat(gym.latitude), parseFloat(gym.longitude)]"
                                >
                                    <l-icon class-name="custom-gym-icon" :icon-anchor="[16, 16]">
                                        <!-- Icono dinámico según si está seleccionado -->
                                        <div 
                                            class="w-8 h-8 flex items-center justify-center border-2 rounded-lg shadow-xl text-lg hover:scale-110 transition-transform cursor-pointer"
                                            :class="isGymSelected(gym.id) 
                                                ? 'bg-indigo-600 border-white text-white scale-110 ring-2 ring-indigo-400' 
                                                : 'bg-gray-900 dark:bg-black border-indigo-500 text-white'"
                                        >
                                            {{ gym.type === 'Crossfit' ? '🔥' : '🏋️' }}
                                        </div>
                                    </l-icon>
                                    
                                    <l-popup :options="{ closeButton: false, offset: [0, -10], className: 'premium-popup' }">
                                        <div class="text-center p-1 w-48 font-sans">
                                            <h4 class="font-bold text-gray-900 text-sm leading-tight mb-0.5">{{ gym.name }}</h4>
                                            
                                            <div v-if="gym.address" class="text-[10px] text-gray-500 mb-1 flex items-start justify-center gap-1">
                                                <span>📍</span> <span class="text-left line-clamp-2 leading-tight">{{ gym.address }}</span>
                                            </div>

                                            <button 
                                                @click="toggleGymSelection(gym.id)"
                                                class="mt-2 w-full text-[10px] font-bold py-2 rounded-lg transition-colors shadow-sm active:scale-95 transform"
                                                :class="isGymSelected(gym.id) 
                                                    ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100' 
                                                    : 'bg-indigo-600 text-white border border-transparent hover:bg-indigo-700'"
                                            >
                                                {{ isGymSelected(gym.id) ? '❌ Dejar este Gym' : '✅ ¡Es mi Gym!' }}
                                            </button>
                                        </div>
                                    </l-popup>
                                </l-marker>
                            </l-map>
                        </div>
                        
                        <!-- Footer con info -->
                        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs text-gray-500">
                             <span>Mostrando gimnasios cercanos</span>
                             <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ selectedGyms.length }} seleccionados</span>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LIcon } from "@vue-leaflet/vue-leaflet";

const props = defineProps({
    show: Boolean,
    userLat: Number,
    userLon: Number,
    gyms: Array,
    selectedGyms: { // Recibimos los IDs seleccionados
        type: Array,
        default: () => []
    } 
});

const emit = defineEmits(['close', 'toggle-gym']);

const zoom = ref(13);
const center = ref([40.4168, -3.7038]); // Madrid default
const ready = ref(false);

watch(() => props.show, async (val) => {
    if (val) {
        ready.value = true;
        if (props.userLat && props.userLon) {
            center.value = [props.userLat, props.userLon];
        }
        
        await nextTick();
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    } else {
        ready.value = false;
    }
});

const isGymSelected = (id) => {
    return props.selectedGyms.includes(id);
};

const toggleGymSelection = (id) => {
    emit('toggle-gym', id);
};
</script>

<style>
/* Estilos globales para popups de Leaflet personalizados */
.premium-popup .leaflet-popup-content-wrapper {
    border-radius: 12px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}
.premium-popup .leaflet-popup-content {
    margin: 8px;
    width: auto !important;
}
.premium-popup .leaflet-popup-tip {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}
.me-popup .leaflet-popup-content-wrapper {
    background: #4f46e5;
    color: white;
    border-radius: 20px;
    padding: 0 4px;
}
.me-popup .leaflet-popup-tip {
    background: #4f46e5;
}

/* Animación Pulse */
.pulse-ring {
    animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulse-ring {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
}
</style>
