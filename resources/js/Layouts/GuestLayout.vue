<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Background decorative elements -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-indigo-500/10 blur-3xl"></div>
            <div class="absolute top-[40%] -right-[10%] w-[40%] h-[40%] rounded-full bg-purple-500/10 blur-3xl"></div>
            <div class="absolute -bottom-[20%] left-[20%] w-[40%] h-[40%] rounded-full bg-pink-500/10 blur-3xl"></div>
        </div>

        <div class="z-10 w-[90%] max-w-sm sm:max-w-md mt-6 px-4 py-6 sm:px-6 sm:py-8 bg-white dark:bg-gray-800 shadow-xl overflow-hidden rounded-3xl border border-gray-100 dark:border-gray-700 relative">
            <div class="flex flex-col items-center mb-4">
                <Link href="/" class="transform hover:scale-105 transition-transform duration-300">
                    <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center bg-white dark:bg-gray-800 p-1">
                        <ApplicationLogo class="h-full w-full" />
                    </div>
                </Link>
            </div>

            <slot />
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500 z-10">
            &copy; {{ new Date().getFullYear() }} GymPal. Todos los derechos reservados.
        </div>
    </div>
</template>
