<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    label: String,
    id: String,
    options: {
        type: Array,
        required: true,
        validator: (value) => value.every(opt => 'value' in opt && 'label' in opt)
    },
    placeholder: {
        type: String,
        default: 'Seleccionar...'
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const selectedOptions = computed(() => {
    return props.options.filter(opt => props.modelValue.includes(opt.value));
});

const selectedLabels = computed(() => {
    if (selectedOptions.value.length === 0) return props.placeholder;
    if (selectedOptions.value.length === 1) return selectedOptions.value[0].label;
    return `${selectedOptions.value.length} seleccionados`;
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const toggleOption = (option) => {
    const newValue = [...props.modelValue];
    const index = newValue.indexOf(option.value);
    
    if (index > -1) {
        newValue.splice(index, 1);
    } else {
        newValue.push(option.value);
    }
    
    emit('update:modelValue', newValue);
};

const isSelected = (option) => {
    return props.modelValue.includes(option.value);
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
        <!-- Label -->
        <label v-if="label" :for="id" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 transition-colors">
            {{ label }}
        </label>

        <!-- Botón Principal -->
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full px-4 py-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 transition-all text-left flex items-center justify-between group outline-none"
            :class="{ 'border-indigo-500 ring-4 ring-indigo-100 dark:ring-indigo-900': isOpen }"
        >
            <div class="flex-1 min-h-[1.5rem]">
                <div class="font-bold text-gray-900 dark:text-white transition-colors">
                    {{ selectedLabels }}
                </div>
                <div v-if="selectedOptions.length > 1" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 flex flex-wrap gap-1 transition-colors">
                    <span 
                        v-for="option in selectedOptions.slice(0, 3)" 
                        :key="option.value"
                        class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium transition-colors"
                    >
                        {{ option.label }}
                    </span>
                    <span v-if="selectedOptions.length > 3" class="text-xs text-gray-500 dark:text-gray-400 transition-colors">
                        +{{ selectedOptions.length - 3 }} más
                    </span>
                </div>
            </div>
            <svg 
                class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-all duration-200"
                :class="{ 'rotate-180': isOpen }"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                class="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-600 shadow-xl max-h-60 overflow-y-auto transition-colors"
            >
                <button
                    v-for="option in options"
                    :key="option.value"
                    type="button"
                    @click="toggleOption(option)"
                    class="w-full px-4 py-3 text-left hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    :class="{
                        'bg-indigo-50 dark:bg-indigo-900/30': isSelected(option)
                    }"
                >
                    <!-- Checkbox -->
                    <div class="flex-shrink-0">
                        <div 
                            class="w-5 h-5 rounded border-2 flex items-center justify-center transition-all"
                            :class="isSelected(option) 
                                ? 'bg-indigo-600 border-indigo-600' 
                                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'"
                        >
                            <svg
                                v-if="isSelected(option)"
                                class="w-3 h-3 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Label -->
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white transition-colors">
                            {{ option.label }}
                        </div>
                    </div>
                </button>
            </div>
        </Transition>
    </div>
</template>
