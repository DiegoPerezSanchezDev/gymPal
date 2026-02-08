<script setup>
const props = defineProps({
    post: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div v-if="post.type === 'achievement' && post.metadata" class="mx-5 mb-6 p-8 relative overflow-hidden rounded-2xl border border-yellow-200/60 dark:border-yellow-700/50 bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 dark:from-amber-900/10 dark:via-orange-900/10 dark:to-yellow-900/10 shadow-sm hover:shadow-md transition-all duration-300 group">
         
         <!-- Decoración de fondo (Glows abstractos) -->
         <div class="absolute -top-12 -right-12 w-40 h-40 bg-yellow-300/20 dark:bg-yellow-600/10 rounded-full blur-3xl pointer-events-none"></div>
         <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-orange-300/20 dark:bg-orange-600/10 rounded-full blur-2xl pointer-events-none"></div>
         
         <div class="relative z-10 flex flex-col items-center text-center">
             <!-- Icono con animación suave -->
             <div class="text-7xl mb-5 filter drop-shadow-sm transform transition-transform duration-500 cubic-bezier(0.34, 1.56, 0.64, 1) group-hover:scale-110 group-hover:rotate-6 cursor-default select-none">
                {{ post.metadata.badge_icon || '🏆' }}
             </div>
             
             <!-- Badge Tag -->
             <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/60 dark:bg-black/20 border border-yellow-200/50 dark:border-yellow-700/30 backdrop-blur-sm mb-3 shadow-sm">
                <span class="text-[10px] sm:text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-widest">
                    Logro Desbloqueado
                </span>
             </div>

             <!-- Título y Descripción -->
             <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 mb-2 tracking-tight">
                {{ post.metadata.badge_name }}
             </h3>
             <p class="text-sm text-gray-600 dark:text-gray-400 font-medium max-w-xs mx-auto leading-relaxed">
                {{ post.metadata.description }}
             </p>
         </div>
    </div>
</template>
