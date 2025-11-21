<template>
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden animate-pulse">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="h-6 bg-gray-200 rounded w-1/4 mb-6"></div>
            <div class="space-y-4">
                <div v-for="n in count" :key="n" class="flex items-center space-x-3 p-2 rounded-md">
                    <div class="h-10 w-10 bg-gray-200 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    count: {
        type: Number,
        default: 5
    }
});
</script>
