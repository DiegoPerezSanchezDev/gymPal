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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-indigo-50 to-purple-50">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-2xl">🏋️</span>
                    Vincular Rutina
                </h3>
                <button @click="$emit('close')" class="p-2 hover:bg-white rounded-full transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-3">
                <!-- Opción: Sin rutina -->
                <button
                    @click="selectWorkout(null)"
                    class="w-full p-4 rounded-xl border-2 transition-all text-left"
                    :class="localSelection === null ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 bg-white'"
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

                <!-- Lista de rutinas -->
                <div v-for="workout in workouts" :key="workout.id">
                    <button
                        @click="selectWorkout(workout.id)"
                        class="w-full p-4 rounded-xl border-2 transition-all text-left"
                        :class="localSelection === workout.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 bg-white'"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0 shadow-md">
                                <span class="text-2xl">{{ categoryEmojis[workout.category] || '🏋️' }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-gray-900">{{ workout.name }}</div>
                                <div class="text-xs text-gray-500">{{ workout.category }}</div>
                            </div>
                            <div v-if="localSelection === workout.id" class="text-indigo-600">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Empty state -->
                <div v-if="!workouts || workouts.length === 0" class="text-center py-8">
                    <div class="text-6xl mb-3">🏋️</div>
                    <p class="text-gray-500 font-medium">No tienes rutinas creadas</p>
                    <p class="text-sm text-gray-400 mt-1">Crea una rutina primero para vincularla</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex gap-3">
                <button
                    @click="$emit('close')"
                    class="flex-1 px-4 py-2.5 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="confirm"
                    class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg transition-all"
                >
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</template>
