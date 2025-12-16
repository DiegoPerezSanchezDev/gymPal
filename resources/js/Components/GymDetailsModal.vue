<script setup>
import { ref, watch, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    gymId: Number,
});

const emit = defineEmits(['close', 'update:gym']);

const gym = ref(null);
const members = ref([]);
const isMember = ref(false);
const isLoading = ref(false);

const fetchGymDetails = async () => {
    if (!props.gymId) return;
    isLoading.value = true;
    try {
        const response = await axios.get(route('gyms.show', props.gymId));
        gym.value = response.data.gym;
        members.value = response.data.members;
        isMember.value = response.data.is_member;
    } catch (error) {
        console.error("Error fetching gym details", error);
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        fetchGymDetails();
    } else {
        // Reset state on close
        gym.value = null;
        members.value = [];
    }
});

const toggleMembership = () => {
    if (!gym.value) return;

    const routeName = isMember.value ? 'gyms.leave' : 'gyms.join';
    
    router.post(route(routeName, gym.value.id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
             isMember.value = !isMember.value;
             // Update local counts
             if (isMember.value) gym.value.users_count++;
             else gym.value.users_count--;
             
             // Emit update to parent to update map marker instantly
             emit('update:gym', { id: gym.value.id, users_count: gym.value.users_count });
             
             // Always refresh members list relative to membership change
             fetchGymDetails();
        }
    });
};

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" maxWidth="md" @close="close">
        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-2xl relative transition-all">
            
            <!-- Skeleton Loading -->
            <div v-if="isLoading && !gym" class="p-8 space-y-4">
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4 animate-pulse"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 animate-pulse"></div>
                <div class="h-32 bg-gray-100 dark:bg-gray-700/50 rounded-xl animate-pulse mt-6"></div>
            </div>

            <div v-else-if="gym">
                <!-- Header / Cover -->
                <div class="relative h-32 bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                    <div class="text-6xl animate-bounce-slow">
                        {{ gym.type === 'pool' ? '🏊' : (gym.type === 'yoga' ? '🧘' : (gym.type === 'crossfit' ? '🔥' : (gym.type === 'park' ? '🤸' : '🏋️'))) }}
                    </div>
                    <button @click="close" class="absolute top-3 right-3 text-white/70 hover:text-white p-1 rounded-full hover:bg-white/10 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <!-- Online User Count Badge -->
                    <div class="absolute top-3 left-3 bg-black/30 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold text-white border border-white/10 flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        {{ gym.users_count }} Miembros
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="text-center -mt-16 mb-4 relative z-10">
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight mb-4">{{ gym.name }}</h2>
                        <span class="inline-block px-3 py-0.5 rounded-full text-[10px] uppercase font-bold tracking-wider bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                            {{ gym.type.toUpperCase() }}
                        </span>
                    </div>

                    <div v-if="gym.address" class="flex items-center justify-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 mb-6">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ gym.address }}
                    </div>

                    <!-- Members Preview -->
                    <div class="mb-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                            <span>GymPals</span>
                            <button class="text-xs text-indigo-500 font-semibold hover:underline">Ver todos</button>
                        </h3>
                        
                        <div v-if="members.length > 0" class="grid grid-cols-4 gap-4">
                            <div v-for="member in members" :key="member.id" class="flex flex-col items-center">
                                <img 
                                    :src="member.profile_picture_url ? '/storage/'+member.profile_picture_url : 'https://ui-avatars.com/api/?name='+encodeURIComponent(member.name)+'&color=7F9CF5&background=EBF4FF'" 
                                    @error="$event.target.src='https://ui-avatars.com/api/?name=Member&color=7F9CF5&background=EBF4FF'"
                                    class="w-12 h-12 rounded-full border-2 border-white dark:border-gray-700 shadow-md object-cover mb-1"
                                >
                                <span class="text-[10px] font-medium text-gray-600 dark:text-gray-400 truncate w-full text-center">
                                    {{ member.username }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-600">
                            <p class="text-sm text-gray-500">Aún no hay gymrats visibles aquí.</p>
                            <p class="text-xs text-indigo-500 font-bold mt-1">¡Sé el primero!</p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="flex justify-center mb-8">
                        <button 
                            @click="toggleMembership"
                            class="w-full max-w-xs py-3 rounded-xl font-bold shadow-lg transform transition-all active:scale-95 flex items-center justify-center gap-2"
                            :class="isMember 
                                ? 'bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/50' 
                                : 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:scale-105'"
                        >
                            <span v-if="isMember">🚫 Dejar este gimnasio</span>
                            <span v-else>✨ ¡Yo entreno aquí!</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Custom Keyframe for slow bounce */
@keyframes bounce-slow {
  0%, 100% {
    transform: translateY(-5%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: translateY(0);
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}
.animate-bounce-slow {
  animation: bounce-slow 3s infinite;
}
</style>
