<template>
  <div class="flex flex-col items-center gap-1 cursor-pointer group" @click="$emit('click')">
    <div 
      class="relative p-[3px] rounded-full transition-all duration-300 transform group-hover:scale-105"
      :class="[
        hasUnseen 
            ? 'bg-gradient-to-tr from-yellow-400 via-orange-500 to-red-600 animate-pulse-slow' 
            : (isMe && !hasStories ? 'border-2 border-dashed border-gray-300 dark:border-gray-600 p-[1px]' : 'bg-gray-200 dark:bg-gray-700')
      ]"
    >
        <div class="bg-white dark:bg-gray-900 border-[3px] border-white dark:border-gray-900 rounded-full overflow-hidden">
            <img 
                :src="image" 
                class="w-16 h-16 object-cover" 
                :class="{ 'opacity-80': isMe && !hasStories }"
            />
        </div>
        
        <!-- Badge "+" para subir si soy yo y no hay stories -->
        <div v-if="isMe && !hasStories" class="absolute bottom-0 right-1 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center border-2 border-white dark:border-gray-900 shadow-sm">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        </div>
    </div>
    <span class="text-xs font-medium truncate w-16 text-center text-gray-700 dark:text-gray-300">{{ isMe ? 'Tu historia' : name }}</span>
  </div>
</template>
<script setup>
defineProps({
    image: String,
    name: String,
    hasUnseen: Boolean,
    isMe: Boolean,
    hasStories: Boolean
});
defineEmits(['click']);
</script>
<style scoped>
.animate-pulse-slow {
    animation: gradient-spin 3s linear infinite;
}
/* Opcional: si quisieramos rotar el gradiente */
</style>
