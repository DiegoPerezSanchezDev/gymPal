<template>
    <div class="container mx-auto px-2 sm:px-4 py-8 animate-pulse">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 relative">
            
            <!-- Header Background -->
            <div class="h-32 bg-gray-200"></div>

            <div class="px-8 pb-8 flex flex-col items-center -mt-20">
                <!-- Avatar -->
                <div class="w-36 h-36 rounded-full bg-gray-300 border-4 border-white shadow-lg mb-4"></div>
                
                <!-- Name -->
                <div class="h-8 bg-gray-300 rounded-lg w-64 mb-2"></div>
                <!-- Username -->
                <div class="h-4 bg-gray-200 rounded w-32 mb-6"></div>
                
                <!-- Stats -->
                <div class="flex gap-8 justify-center mb-8 w-full border-t border-b border-gray-100 py-4">
                    <div class="flex flex-col items-center gap-2">
                        <div class="h-8 w-12 bg-gray-200 rounded"></div>
                        <div class="h-3 w-20 bg-gray-100 rounded"></div>
                    </div>
                    <div class="flex flex-col items-center gap-2 border-l border-gray-100 pl-8">
                        <div class="h-8 w-12 bg-gray-200 rounded"></div>
                        <div class="h-3 w-20 bg-gray-100 rounded"></div>
                    </div>
                </div>

                <!-- Bio -->
                <div class="space-y-2 w-full max-w-lg mb-8">
                    <div class="h-4 bg-gray-100 rounded w-full"></div>
                    <div class="h-4 bg-gray-100 rounded w-5/6 mx-auto"></div>
                </div>

                <!-- Interests -->
                <div class="flex flex-wrap justify-center gap-2 mb-8">
                    <div class="h-8 w-24 bg-gray-200 rounded-full"></div>
                    <div class="h-8 w-32 bg-gray-200 rounded-full"></div>
                    <div class="h-8 w-20 bg-gray-200 rounded-full"></div>
                </div>

                <!-- Actions -->
                <div class="w-full max-w-sm space-y-3">
                     <div class="h-12 bg-gray-200 rounded-xl w-full"></div>
                </div>
            </div>
        </div>
    </div>
</template>
