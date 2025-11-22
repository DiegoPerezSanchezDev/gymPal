<script setup>
import { useToast } from '@/composables/useToast';
import Toast from '@/Components/Toast.vue';

const { toasts, removeToast } = useToast();
</script>

<template>
    <div class="fixed top-20 right-4 z-[9999] space-y-3 pointer-events-none">
        <div v-for="toast in toasts" :key="toast.id" class="pointer-events-auto">
            <Toast
                :show="toast.show"
                :message="toast.message"
                :type="toast.type"
                :duration="toast.duration"
                @close="removeToast(toast.id)"
            />
        </div>
    </div>
</template>
