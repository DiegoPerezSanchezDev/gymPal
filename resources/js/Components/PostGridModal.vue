<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import PostCard from './PostCard.vue';

const props = defineProps({
    posts: {
        type: Array,
        required: true
    },
    initialPostIndex: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['close']);

const currentIndex = ref(props.initialPostIndex);
const modalRef = ref(null);

const currentPost = computed(() => props.posts[currentIndex.value]);
const hasPrevious = computed(() => currentIndex.value > 0);
const hasNext = computed(() => currentIndex.value < props.posts.length - 1);

const goToPrevious = () => {
    if (hasPrevious.value) {
        currentIndex.value--;
    }
};

const goToNext = () => {
    if (hasNext.value) {
        currentIndex.value++;
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Escape') emit('close');
    if (e.key === 'ArrowLeft') goToPrevious();
    if (e.key === 'ArrowRight') goToNext();
};

// Auto-focus modal for keyboard navigation
onMounted(() => {
    if (modalRef.value) {
        modalRef.value.focus();
    }
});

// Keyboard navigation
watch(() => props.initialPostIndex, (newIndex) => {
    currentIndex.value = newIndex;
}, { immediate: true });
</script>

<template>
    <div 
        ref="modalRef"
        @click.self="$emit('close')" 
        @keydown="handleKeydown"
        tabindex="0"
        class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
    >
        <!-- Close Button -->
        <button 
            @click="$emit('close')" 
            class="absolute top-4 right-4 z-50 text-white/80 hover:text-white bg-black/30 hover:bg-black/50 rounded-full p-2 transition-all backdrop-blur-sm"
        >
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Navigation Arrows -->
        <button 
            v-if="hasPrevious"
            @click="goToPrevious"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-50 text-white/80 hover:text-white bg-black/30 hover:bg-black/50 rounded-full p-3 transition-all backdrop-blur-sm"
        >
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <button 
            v-if="hasNext"
            @click="goToNext"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-50 text-white/80 hover:text-white bg-black/30 hover:bg-black/50 rounded-full p-3 transition-all backdrop-blur-sm"
        >
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Post Container -->
        <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar" @click.stop>
            <PostCard :post="currentPost" :isDetailView="true" />
        </div>

        <!-- Counter -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/90 text-sm font-medium bg-black/40 px-4 py-2 rounded-full backdrop-blur-sm">
            {{ currentIndex + 1 }} / {{ posts.length }}
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>
