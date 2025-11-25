<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: '¿Estás seguro?'
    },
    message: {
        type: String,
        required: true
    },
    confirmText: {
        type: String,
        default: 'Confirmar'
    },
    cancelText: {
        type: String,
        default: 'Cancelar'
    },
    type: {
        type: String,
        default: 'warning', // warning, danger, info
        validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value)
    }
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const typeConfig = {
    warning: {
        icon: '⚠️',
        gradient: 'from-yellow-500 to-orange-600',
        bgGradient: 'from-yellow-50 to-orange-50',
        border: 'border-yellow-200'
    },
    danger: {
        icon: '🗑️',
        gradient: 'from-red-500 to-pink-600',
        bgGradient: 'from-red-50 to-pink-50',
        border: 'border-red-200'
    },
    info: {
        icon: 'ℹ️',
        gradient: 'from-blue-500 to-indigo-600',
        bgGradient: 'from-blue-50 to-indigo-50',
        border: 'border-blue-200'
    },
    success: {
        icon: '✅',
        gradient: 'from-green-500 to-emerald-600',
        bgGradient: 'from-green-50 to-emerald-50',
        border: 'border-green-200'
    }
};

const config = typeConfig[props.type] || typeConfig.warning;
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
            @click.self="$emit('cancel')"
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
                    class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                >
                    <!-- Header con gradiente -->
                    <div :class="['p-6 bg-gradient-to-br', config.bgGradient, config.border, 'border-b']">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-white shadow-md flex items-center justify-center text-3xl">
                                {{ config.icon }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-black text-gray-900">{{ title }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-gray-700 leading-relaxed">{{ message }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 pb-6 flex gap-3">
                        <button
                            @click="$emit('cancel')"
                            class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 active:scale-95 transition-all"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            @click="$emit('confirm')"
                            :class="[
                                'flex-1 px-4 py-3 text-white font-bold rounded-xl active:scale-95 transition-all shadow-md',
                                'bg-gradient-to-r',
                                config.gradient
                            ]"
                        >
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
