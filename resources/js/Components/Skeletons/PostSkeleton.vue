<template>
    <div class="animate-pulse bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-100">
        <!-- Header -->
        <div class="p-4 flex items-center space-x-3">
            <div class="rounded-full bg-gray-200 h-10 w-10"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                <div class="h-3 bg-gray-200 rounded w-1/4"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-4 pb-2 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>

        <!-- Image Placeholder -->
        <div class="w-full h-64 bg-gray-200 mt-2"></div>

        <!-- Footer Actions -->
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-100 mt-2">
            <div class="flex space-x-4">
                <div class="h-6 w-6 bg-gray-200 rounded-full"></div>
                <div class="h-6 w-6 bg-gray-200 rounded-full"></div>
                <div class="h-6 w-6 bg-gray-200 rounded-full"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded w-16"></div>
        </div>
    </div>
</template>
