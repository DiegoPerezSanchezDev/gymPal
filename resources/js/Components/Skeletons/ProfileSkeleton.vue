<template>
    <div class="container mx-auto px-2 sm:px-4 py-8 animate-pulse">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700 relative transition-colors">
            
            <!-- Header Background -->
            <div class="h-32 bg-gray-200 dark:bg-gray-700"></div>

            <div class="px-8 pb-8 flex flex-col items-center -mt-20">
                <!-- Avatar -->
                <div class="w-36 h-36 rounded-full bg-gray-300 dark:bg-gray-600 border-4 border-white dark:border-gray-800 shadow-lg mb-4 transition-colors"></div>
                
                <!-- Name -->
                <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded-lg w-64 mb-2"></div>
                <!-- Username -->
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-6"></div>
                
                <!-- Stats -->
                <div class="flex gap-8 justify-center mb-8 w-full border-t border-b border-gray-100 dark:border-gray-700 py-4 transition-colors">
                    <div class="flex flex-col items-center gap-2">
                        <div class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-20 bg-gray-100 dark:bg-gray-600 rounded"></div>
                    </div>
                    <div class="flex flex-col items-center gap-2 border-l border-gray-100 dark:border-gray-700 pl-8 transition-colors">
                        <div class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-20 bg-gray-100 dark:bg-gray-600 rounded"></div>
                    </div>
                </div>

                <!-- Bio -->
                <div class="space-y-2 w-full max-w-lg mb-8">
                    <div class="h-4 bg-gray-100 dark:bg-gray-600 rounded w-full"></div>
                    <div class="h-4 bg-gray-100 dark:bg-gray-600 rounded w-5/6 mx-auto"></div>
                </div>

                <!-- Interests -->
                <div class="flex flex-wrap justify-center gap-2 mb-8">
                    <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    <div class="h-8 w-32 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    <div class="h-8 w-20 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                </div>

                <!-- Actions -->
                <div class="w-full max-w-sm space-y-3">
                     <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded-xl w-full"></div>
                </div>
            </div>
        </div>
    </div>
</template>
