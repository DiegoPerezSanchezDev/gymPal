<template>
    <div class="bg-white overflow-hidden shadow-md rounded-lg p-6 mb-6">
        <div class="h-6 bg-gray-200 rounded w-1/3 mb-4 animate-pulse"></div>
        <div class="space-y-4">
            <div v-for="n in count" :key="n" class="flex items-center justify-between p-3 border border-gray-50 rounded-md">
                <div class="flex items-center gap-4 w-full">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex-shrink-0 animate-pulse"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/3 animate-pulse"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/4 animate-pulse"></div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <div class="h-8 w-20 bg-gray-200 rounded animate-pulse"></div>
                    <div class="h-8 w-20 bg-gray-200 rounded animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    count: {
        type: Number,
        default: 3
    }
});
</script>
