<template>
    <div class="container mx-auto px-2 sm:px-4 py-8 animate-pulse">
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
            <!-- Avatar -->
            <div class="w-32 h-32 rounded-full bg-gray-200 mb-4"></div>
            
            <!-- Name & Username -->
            <div class="h-8 bg-gray-200 rounded w-1/2 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
            
            <!-- Bio -->
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-2/3 mb-4"></div>

            <!-- Tags/Interests -->
            <div class="flex gap-2 mb-6">
                <div class="h-6 w-16 bg-gray-200 rounded-full"></div>
                <div class="h-6 w-20 bg-gray-200 rounded-full"></div>
                <div class="h-6 w-14 bg-gray-200 rounded-full"></div>
            </div>

            <!-- Stats -->
            <div class="flex gap-6 justify-center mb-6">
                <div class="flex flex-col items-center gap-1">
                    <div class="h-6 w-8 bg-gray-200 rounded"></div>
                    <div class="h-3 w-16 bg-gray-200 rounded"></div>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <div class="h-6 w-8 bg-gray-200 rounded"></div>
                    <div class="h-3 w-16 bg-gray-200 rounded"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="w-full max-w-xs h-10 bg-gray-200 rounded"></div>
        </div>
    </div>
</template>
