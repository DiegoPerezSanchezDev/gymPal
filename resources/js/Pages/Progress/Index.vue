<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: String,
    workoutsPerWeek: Array,
    minutesPerWeek: Array,
    personalRecords: Object
});

// Helper para calcular altura de barras
const getMax = (arr, key) => {
    if (!arr || arr.length === 0) return 1;
    return Math.max(...arr.map(item => item[key]));
};

const maxWorkouts = computed(() => getMax(props.workoutsPerWeek, 'count'));
const maxMinutes = computed(() => getMax(props.minutesPerWeek, 'minutes'));

</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center shadow-md text-white">
                    <span class="text-2xl">📈</span>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                        Mi Progreso
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Analiza tu evolución</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 space-y-8">
                
                <!-- Gráfico 1: Entrenamientos por Semana -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="w-2 h-6 bg-indigo-500 rounded-full"></span>
                        Entrenamientos por Semana
                    </h3>
                    
                    <div class="h-48 flex items-end justify-between gap-2">
                        <div v-for="(week, index) in workoutsPerWeek" :key="index" class="flex-1 flex flex-col items-center group">
                            <div class="relative w-full flex justify-center items-end h-full">
                                <div 
                                    class="w-full max-w-[30px] bg-indigo-100 rounded-t-lg transition-all duration-500 group-hover:bg-indigo-200 relative"
                                    :style="{ height: (week.count / maxWorkouts * 100) + '%' }"
                                >
                                    <!-- Tooltip -->
                                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                        {{ week.count }} entrenos
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-400 mt-2 font-medium">{{ week.week_label }}</span>
                        </div>
                        <!-- Empty State si no hay datos -->
                        <div v-if="workoutsPerWeek.length === 0" class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                            No hay datos suficientes
                        </div>
                    </div>
                </div>

                <!-- Gráfico 2: Minutos por Semana -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                        Tiempo de Entrenamiento (min)
                    </h3>
                    
                    <div class="h-48 flex items-end justify-between gap-2">
                        <div v-for="(week, index) in minutesPerWeek" :key="index" class="flex-1 flex flex-col items-center group">
                            <div class="relative w-full flex justify-center items-end h-full">
                                <div 
                                    class="w-full max-w-[30px] bg-emerald-100 rounded-t-lg transition-all duration-500 group-hover:bg-emerald-200 relative"
                                    :style="{ height: (week.minutes / maxMinutes * 100) + '%' }"
                                >
                                    <!-- Tooltip -->
                                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                        {{ week.minutes }} min
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-400 mt-2 font-medium">{{ week.week_label }}</span>
                        </div>
                        <div v-if="minutesPerWeek.length === 0" class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                            No hay datos suficientes
                        </div>
                    </div>
                </div>

                <!-- Récords Personales -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="w-2 h-6 bg-yellow-400 rounded-full"></span>
                        Mejores Pesos (Top 5)
                    </h3>
                    
                    <div class="space-y-4">
                        <div v-for="(weight, exercise) in personalRecords" :key="exercise" class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center font-bold text-xs">
                                    🏆
                                </div>
                                <span class="font-bold text-gray-700">{{ exercise }}</span>
                            </div>
                            <span class="font-black text-gray-900 text-lg">{{ weight }} <span class="text-xs text-gray-500 font-normal">kg</span></span>
                        </div>
                        <div v-if="Object.keys(personalRecords).length === 0" class="text-center text-gray-400 py-4">
                            Registra tus entrenamientos para ver tus récords
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
