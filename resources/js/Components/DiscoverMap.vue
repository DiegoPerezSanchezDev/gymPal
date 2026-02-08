<script setup>
import { computed, ref } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LIcon, LCircle } from "@vue-leaflet/vue-leaflet";

const props = defineProps({
    gyms: Array,
    mapCenter: Array,
    zoom: Number,
    formLat: [Number, String, null],
    formLon: [Number, String, null]
});

const emit = defineEmits([
    'update:zoom', 
    'map-ready', 
    'map-moveend', 
    'open-gym-modal',
    'open-gym-members-modal'
]);

const map = ref(null);

const onMapReady = (mapObject) => {
    emit('map-ready', mapObject);
};

const onMapMoveEnd = (e) => {
    emit('map-moveend', e);
};

defineExpose({
    leafletObject: computed(() => map.value?.leafletObject)
});

</script>

<template>
    <div class="relative h-[500px] md:h-[650px] w-full rounded-2xl overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-700 transition-all duration-500 z-0">
        
        <!-- Controles Flotantes (Glassmorphism) -->
        <div class="absolute top-4 right-4 z-[400] flex flex-col gap-2">
            <button v-if="formLat" @click="map?.leafletObject.flyTo([formLat, formLon], 14)" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-3 rounded-full shadow-lg hover:scale-110 transition-transform text-indigo-600 dark:text-indigo-400" title="Mi ubicación">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
        </div>

        <l-map 
            ref="map" 
            :zoom="zoom"
            @update:zoom="emit('update:zoom', $event)"
            :center="mapCenter" 
            :use-global-leaflet="false" 
            :options="{zoomControl: false}"
            @ready="onMapReady"
            @moveend="onMapMoveEnd"
        >
            <l-tile-layer
                url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                layer-type="base"
                name="CartoDB Voyager"
            ></l-tile-layer>

            <!-- Círculo de mi ubicación -->
            <l-circle 
                v-if="formLat && formLon"
                :lat-lng="[formLat, formLon]" 
                :radius="500" 
                color="#4f46e5" 
                fillColor="#6366f1" 
                :fillOpacity="0.1" 
            />

            <!-- Marcadores de Gimnasios -->
            <l-marker 
                v-for="gym in gyms" 
                :key="'gym-'+gym.id" 
                :lat-lng="[gym.lat, gym.lon]"
            >
                <l-icon :icon-size="[40, 40]" :icon-anchor="[20, 40]">
                    <div class="relative group">
                        <div :class="['w-10 h-10 rounded-full bg-white dark:bg-gray-800 border-2 shadow-lg flex items-center justify-center text-xl transition-all group-hover:scale-110', gym.colorClass]">
                             {{ gym.icon }}
                        </div>
                        <div v-if="gym.users_count > 0" class="absolute -top-1 -right-1 bg-indigo-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center border border-white dark:border-gray-800 shadow-sm animate-pulse">
                            {{ gym.users_count }}
                        </div>
                    </div>
                </l-icon>
                <l-popup>
                    <div class="p-2 min-w-[200px]">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ gym.type }}</p>
                        <h4 class="font-black text-gray-900 dark:text-white text-base leading-tight">{{ gym.name }}</h4>
                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ gym.address }}
                        </p>
                        
                        <div v-if="gym.users_count > 0" class="mt-3 flex items-center gap-2">
                             <div class="flex -space-x-2">
                                <div v-for="i in Math.min(3, gym.users_count)" :key="i" class="w-6 h-6 rounded-full border border-white dark:border-gray-800 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[8px]">👤</div>
                             </div>
                             <button @click="emit('open-gym-members-modal', gym)" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ gym.users_count }} personas entrenando aquí
                             </button>
                        </div>

                        <div class="mt-4 flex flex-col gap-2">
                            <button @click="emit('open-gym-modal', gym.id)" class="w-full py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition shadow-sm">
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </l-popup>
            </l-marker>

        </l-map>
    </div>
</template>

<style>
/* Estilos para el popup de Leaflet en modo dark */
.dark .leaflet-popup-content-wrapper, 
.dark .leaflet-popup-tip {
    background-color: #1f2937 !important;
    color: white !important;
}
</style>
