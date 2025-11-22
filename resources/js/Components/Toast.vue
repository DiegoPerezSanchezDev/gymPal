<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    type: {
        type: String,
        default: 'info', // 'success', 'error', 'warning', 'info'
    },
    message: {
        type: String,
        required: true,
    },
    duration: {
        type: Number,
        default: 3000,
    }
});

const emit = defineEmits(['close']);

const visible = ref(false);
let timeoutId = null;

const iconPath = computed(() => {
    switch (props.type) {
        case 'success':
            return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
        case 'error':
            return 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
        case 'warning':
            return 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
        default: // info
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }
});

const colorClasses = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-50 border-green-200 text-green-800';
        case 'error':
            return 'bg-red-50 border-red-200 text-red-800';
        case 'warning':
            return 'bg-yellow-50 border-yellow-200 text-yellow-800';
        default: // info
            return 'bg-blue-50 border-blue-200 text-blue-800';
    }
});

const iconColorClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-500';
        case 'error':
            return 'text-red-500';
        case 'warning':
            return 'text-yellow-500';
        default:
            return 'text-blue-500';
    }
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        visible.value = true;
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            close();
        }, props.duration);
    }
});

onMounted(() => {
    if (props.show) {
        visible.value = true;
        timeoutId = setTimeout(() => {
            close();
        }, props.duration);
    }
});

const close = () => {
    visible.value = false;
    setTimeout(() => {
        emit('close');
    }, 300);
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-2 opacity-0"
    >
        <div
            v-if="visible"
            :class="colorClasses"
            class="max-w-sm w-full shadow-2xl rounded-xl border-2 p-4"
        >
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <svg :class="iconColorClass" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
                    </svg>
                </div>

                <!-- Message -->
                <div class="flex-1 pt-0.5">
                    <p class="text-sm font-semibold leading-snug">{{ message }}</p>
                </div>

                <!-- Close Button -->
                <button
                    @click="close"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
