<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: String,
    options: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const getTypeStyles = (type) => {
    switch(type) {
        case 'warmup': return 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800';
        case 'failure': return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';
        case 'drop': return 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800';
        default: return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800';
    }
};

const getTypeIcon = (type) => {
    switch(type) {
        case 'warmup': return '🔥';
        case 'failure': return '💀';
        case 'drop': return '⬇️';
        default: return '✅';
    }
};

const getTypeChar = (type) => {
    switch(type) {
        case 'warmup': return 'C';
        case 'failure': return 'F';
        case 'drop': return 'D';
        default: return 'N';
    }
};

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue) || props.options[0];
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const selectOption = (option) => {
    emit('update:modelValue', option.value);
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Botón Compacto con Identidad Visual Fuerte -->
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full h-9 px-2 rounded-xl border-2 transition-all flex items-center justify-between outline-none shadow-sm"
            :class="[
                getTypeStyles(modelValue),
                isOpen ? 'ring-2 ring-indigo-300 dark:ring-indigo-700' : ''
            ]"
        >
            <div class="flex items-center gap-1.5 min-w-0">
                <span class="text-xs font-black">{{ getTypeChar(modelValue) }}</span>
                <span class="text-[10px] font-bold uppercase truncate tracking-tighter hidden sm:inline">
                    {{ selectedOption?.label }}
                </span>
            </div>
            <svg 
                class="w-3 h-3 opacity-60 flex-shrink-0"
                :class="{ 'rotate-180': isOpen }"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 w-32 left-1/2 -translate-x-1/2 sm:left-0 sm:translate-x-0 sm:w-full bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-600 shadow-xl overflow-hidden transition-colors"
            >
                <button
                    v-for="option in options"
                    :key="option.value"
                    type="button"
                    @click="selectOption(option)"
                    class="w-full px-3 py-2.5 text-xs text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    :class="{
                        'font-black bg-indigo-50/50 dark:bg-indigo-900/20': option.value === modelValue
                    }"
                >
                    <span :class="getTypeStyles(option.value)" class="w-6 h-6 rounded-lg flex items-center justify-center text-xs border shrink-0 shadow-sm">
                        {{ getTypeIcon(option.value) }}
                    </span>
                    <span class="text-gray-900 dark:text-white font-bold leading-none">
                        {{ option.label }}
                    </span>
                </button>
            </div>
        </Transition>
    </div>
</template>
