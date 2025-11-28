<template>
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden animate-pulse transition-colors">
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors">
            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-6"></div>
            <div class="space-y-4">
                <div v-for="n in count" :key="n" class="flex items-center space-x-3 p-2 rounded-md">
                    <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
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
