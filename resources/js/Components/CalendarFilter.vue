<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: Object, // { start: null, end: null }
    activityDates: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const currentDate = ref(new Date());
const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const days = [];
    
    // Días vacíos al inicio
    for (let i = 0; i < startingDayOfWeek; i++) {
        days.push({ day: null, fullDate: null });
    }
    
    // Días del mes
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        days.push({
            day,
            fullDate: dateStr,
            hasActivity: props.activityDates.includes(dateStr)
        });
    }
    
    return days;
});

function previousMonth() {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
}

function nextMonth() {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
}

function selectDate(date) {
    if (!date) return;
    
    const currentStart = props.modelValue?.start;
    const currentEnd = props.modelValue?.end;

    let newRange = { ...props.modelValue };

    if (!currentStart || (currentStart && currentEnd)) {
        // Empezar nueva selección
        newRange = { start: date, end: null };
    } else {
        // Completar rango
        if (date < currentStart) {
            newRange = { start: date, end: currentStart };
        } else {
            newRange = { start: currentStart, end: date };
        }
    }
    
    emit('update:modelValue', newRange);
}

function isSelected(date) {
    if (!date || !props.modelValue?.start) return false;
    if (date === props.modelValue.start || date === props.modelValue.end) return true;
    return false;
}

function isInRange(date) {
    if (!date || !props.modelValue?.start || !props.modelValue?.end) return false;
    return date > props.modelValue.start && date < props.modelValue.end;
}

function clearSelection() {
    emit('update:modelValue', { start: null, end: null });
}
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gray-50">
            <button @click="previousMonth" class="p-1 hover:bg-gray-200 rounded-lg transition text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <span class="font-bold text-gray-800">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
            <button @click="nextMonth" class="p-1 hover:bg-gray-200 rounded-lg transition text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>

        <!-- Days Header -->
        <div class="grid grid-cols-7 gap-1 p-2 border-b border-gray-100">
            <div v-for="day in dayNames" :key="day" class="text-center text-xs font-bold text-gray-400 py-1">
                {{ day }}
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1 p-2">
            <div 
                v-for="(dayObj, index) in calendarDays" 
                :key="index"
                class="aspect-square relative"
            >
                <button
                    v-if="dayObj.day"
                    @click="selectDate(dayObj.fullDate)"
                    class="w-full h-full rounded-lg flex items-center justify-center text-sm font-medium transition-all relative z-10"
                    :class="[
                        isSelected(dayObj.fullDate) ? 'bg-indigo-600 text-white shadow-md transform scale-105' : 
                        isInRange(dayObj.fullDate) ? 'bg-indigo-100 text-indigo-700' : 
                        'text-gray-700 hover:bg-gray-100',
                        dayObj.hasActivity && !isSelected(dayObj.fullDate) && !isInRange(dayObj.fullDate) ? 'font-bold' : ''
                    ]"
                >
                    {{ dayObj.day }}
                    
                    <!-- Activity Dot -->
                    <span 
                        v-if="dayObj.hasActivity" 
                        class="absolute bottom-1 w-1 h-1 rounded-full"
                        :class="isSelected(dayObj.fullDate) ? 'bg-white' : 'bg-green-500'"
                    ></span>
                </button>
            </div>
        </div>

        <!-- Footer / Legend -->
        <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-gray-500">Día entrenado</span>
            </div>
            <button 
                v-if="modelValue?.start || modelValue?.end"
                @click="clearSelection" 
                class="text-indigo-600 font-bold hover:underline"
            >
                Limpiar filtro
            </button>
        </div>
    </div>
</template>
