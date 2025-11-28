<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number, null],
    label: String,
    id: String,
    options: {
        type: Array,
        required: true,
        validator: (value) => value.every(opt => 'value' in opt && 'label' in opt)
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const dropdownRef = ref(null);

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
                    {{ selectedOption?.label || 'Seleccionar...' }}
                </div>
                <div v-if="selectedOption?.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 transition-colors">
                    {{ selectedOption.description }}
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
                    @click="selectOption(option)"
                    class="w-full px-4 py-3 text-left hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center justify-between group border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    :class="{
                        'bg-indigo-50 dark:bg-indigo-900/30': option.value === modelValue
                    }"
                >
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white transition-colors">
                            {{ option.label }}
                        </div>
                        <div v-if="option.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 transition-colors">
                            {{ option.description }}
                        </div>
                    </div>
                    <svg
                        v-if="option.value === modelValue"
                        class="w-5 h-5 text-indigo-600 dark:text-indigo-400 transition-colors"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </Transition>
    </div>
</template>