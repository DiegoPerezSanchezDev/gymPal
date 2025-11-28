<template>
    <div class="animate-pulse bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-2xl mb-6 border border-gray-100 dark:border-gray-700 transition-colors">
        <!-- Header -->
        <div class="p-5 flex items-center space-x-3">
            <div class="rounded-full bg-gray-200 dark:bg-gray-700 h-12 w-12"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                <div class="h-3 bg-gray-100 dark:bg-gray-600 rounded w-24"></div>
            </div>
            <div class="h-6 w-6 bg-gray-100 dark:bg-gray-600 rounded-full"></div> <!-- Menu dots -->
        </div>

        <!-- Content -->
        <div class="px-5 pb-4 space-y-3">
            <div class="h-4 bg-gray-100 dark:bg-gray-600 rounded w-full"></div>
            <div class="h-4 bg-gray-100 dark:bg-gray-600 rounded w-5/6"></div>
            <div class="h-4 bg-gray-100 dark:bg-gray-600 rounded w-4/6"></div>
        </div>

        <!-- Image Placeholder -->
        <div class="w-full h-72 bg-gray-200 dark:bg-gray-700"></div>

        <!-- Footer Actions -->
        <div class="px-5 py-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 transition-colors">
            <div class="flex-1 flex justify-center">
                <div class="h-8 w-20 bg-gray-100 dark:bg-gray-600 rounded-lg"></div>
            </div>
            <div class="flex-1 flex justify-center border-l border-gray-100 dark:border-gray-700 transition-colors">
                <div class="h-8 w-20 bg-gray-100 dark:bg-gray-600 rounded-lg"></div>
            </div>
            <div class="flex-1 flex justify-center border-l border-gray-100 dark:border-gray-700 transition-colors">
                <div class="h-8 w-20 bg-gray-100 dark:bg-gray-600 rounded-lg"></div>
            </div>
        </div>
    </div>
</template>
