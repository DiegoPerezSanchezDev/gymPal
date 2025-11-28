<template>
    <div class="animate-pulse space-y-4">
        <!-- Skeleton para cada conversación -->
        <div v-for="n in 5" :key="n" class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
            <!-- Avatar skeleton -->
            <div class="w-14 h-14 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
            
            <!-- Contenido skeleton -->
            <div class="flex-1 space-y-2">
                <div class="flex justify-between items-start">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-12"></div>
                </div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-48"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
// No props needed for skeleton
</script>
