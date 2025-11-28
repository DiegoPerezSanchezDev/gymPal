<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    workouts: Array,
    selectedWorkoutId: Number
});

const emit = defineEmits(['close', 'select']);

const localSelection = ref(props.selectedWorkoutId);

const selectWorkout = (workoutId) => {
    localSelection.value = workoutId;
};

const confirm = () => {
    emit('select', localSelection.value);
    emit('close');
};

const categoryEmojis = {
    'Fuerza': '💪',
    'Cardio': '🏃',
    'Flexibilidad': '🧘',
    'Mixto': '🔥',
    'Otro': '🏋️'
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="show" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" 
            @click.self="$emit('close')"
            role="dialog"
            aria-modal="true"
            aria-labelledby="workout-selector-title"
        >
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div 
                    v-if="show"
                    class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-2xl">
                        <h3 id="workout-selector-title" class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="text-2xl">🏋️</span>
                            Vincular Rutina
                        </h3>
                        <button 
                            @click="$emit('close')" 
                            class="p-2 hover:bg-white rounded-lg transition-colors"
                            aria-label="Cerrar modal"
                        >
                            <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content with improved scroll -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-3 custom-scrollbar">
                        <!-- Opción: Sin rutina -->
                        <button
                            @click="selectWorkout(null)"
                            class="w-full p-4 rounded-xl border-2 transition-all text-left hover:shadow-md"
                            :class="localSelection === null ? 'border-indigo-500 bg-indigo-50 shadow-md' : 'border-gray-200 hover:border-gray-300 bg-white'"
                            aria-label="Sin rutina vinculada"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                    <span class="text-2xl">🚫</span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-900">Sin rutina vinculada</div>
                                    <div class="text-xs text-gray-500">Publicación normal sin referencia</div>
                                </div>
                                <div v-if="localSelection === null" class="text-indigo-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </button>

                        <!-- Separador -->
                        <div v-if="workouts && workouts.length > 0" class="relative py-2">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-3 bg-white text-xs font-bold text-gray-400 uppercase tracking-wider">Tus Rutinas</span>
                            </div>
                        </div>

                        <!-- Lista de rutinas -->
                        <div v-for="workout in workouts" :key="workout.id">
                            <button
                                @click="selectWorkout(workout.id)"
                                class="w-full p-4 rounded-xl border-2 transition-all text-left hover:shadow-md"
                                :class="localSelection === workout.id ? 'border-indigo-500 bg-indigo-50 shadow-md' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                :aria-label="`Seleccionar rutina ${workout.name}`"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0 shadow-md">
                                        <span class="text-2xl">{{ categoryEmojis[workout.category] || '🏋️' }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-900 truncate">{{ workout.name }}</div>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span>{{ workout.category }}</span>
                                            <span v-if="workout.difficulty" class="text-gray-400">•</span>
                                            <span v-if="workout.difficulty" class="capitalize">{{ workout.difficulty }}</span>
                                        </div>
                                    </div>
                                    <div v-if="localSelection === workout.id" class="text-indigo-600 shrink-0">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!workouts || workouts.length === 0" class="text-center py-12">
                            <div class="text-6xl mb-4">🏋️</div>
                            <p class="text-gray-700 font-bold text-lg mb-1">No tienes rutinas creadas</p>
                            <p class="text-sm text-gray-500">Crea una rutina primero para vincularla a tus publicaciones</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl flex gap-3">
                        <button
                            @click="$emit('close')"
                            class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-white transition-all"
                            aria-label="Cancelar selección"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="confirm"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all flex items-center justify-center gap-2"
                            aria-label="Confirmar selección de rutina"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Confirmar
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
/* Custom scrollbar para mejor UX */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c7d2fe;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a5b4fc;
}
</style>
