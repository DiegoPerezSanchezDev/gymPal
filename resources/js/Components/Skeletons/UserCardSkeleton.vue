<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden animate-pulse flex flex-col h-full transition-colors">
        <!-- Header / Avatar Area -->
        <div class="p-6 flex flex-col items-center flex-grow">
            <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-full mb-4 transition-colors"></div>
            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2 transition-colors"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4 transition-colors"></div>
            
            <!-- Tags -->
            <div class="flex gap-2 mb-4 w-full justify-center">
                <div class="h-6 w-12 bg-gray-200 dark:bg-gray-700 rounded-full transition-colors"></div>
                <div class="h-6 w-16 bg-gray-200 dark:bg-gray-700 rounded-full transition-colors"></div>
            </div>
        </div>

        <!-- Footer / Actions -->
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-100 dark:border-gray-700 mt-auto transition-colors">
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full transition-colors"></div>
        </div>
    </div>
</template>
