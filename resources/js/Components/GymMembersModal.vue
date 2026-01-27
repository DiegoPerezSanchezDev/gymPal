<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    gym: Object
});

const emit = defineEmits(['close']);

const members = ref([]);
const loading = ref(false);
const nextCursor = ref(null);
const page = ref(1);
const hasMore = ref(true);

async function fetchMembers(reset = false) {
    if (loading.value) return;
    if (reset) {
        page.value = 1;
        members.value = [];
        hasMore.value = true;
    }
    if (!hasMore.value) return;

    loading.value = true;
    try {
        const response = await axios.get(route('gyms.members', props.gym.id), {
            params: { page: page.value }
        });
        
        const data = response.data;
        members.value = [...members.value, ...data.data];
        hasMore.value = data.current_page < data.last_page;
        page.value++;
    } catch (e) {
        console.error('Error fetching gym members:', e);
    } finally {
        loading.value = false;
    }
}

watch(() => props.show, (newVal) => {
    if (newVal && props.gym) {
        fetchMembers(true);
    }
});

function close() {
    emit('close');
}
</script>

<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white transition-colors">GymPals en {{ gym?.name }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Personas que entrenan aquí</p>
                </div>
                <button @click="close" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                <div v-for="user in members" :key="user.id" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900 transition-all">
                    <Link :href="route('profile.show.public', user.username)" class="flex items-center gap-3">
                        <div class="relative">
                            <img 
                                :src="user.profile_picture_url ? '/storage/'+user.profile_picture_url : 'https://ui-avatars.com/api/?name='+encodeURIComponent(user.name)+'&color=7F9CF5&background=EBF4FF'" 
                                class="w-12 h-12 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-sm"
                                @error="$event.target.src='https://ui-avatars.com/api/?name=User&color=7F9CF5&background=EBF4FF'"
                            >
                            <div v-if="user.is_following_me" class="absolute -bottom-1 -right-1 bg-indigo-600 text-white text-[8px] font-black px-1 py-0.5 rounded-full border border-white dark:border-gray-800 uppercase tracking-tighter">
                                Te sigue
                            </div>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white transition-colors">{{ user.name }}</p>
                            <p class="text-xs text-indigo-500 dark:text-indigo-400 font-bold transition-colors">@{{ user.username }}</p>
                        </div>
                    </Link>
                    
                    <Link 
                        :href="route('profile.show.public', user.username)" 
                        class="px-4 py-1.5 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm"
                    >
                        Ver Perfil
                    </Link>
                </div>

                <div v-if="loading" class="flex justify-center py-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>

                <button 
                    v-if="hasMore && !loading" 
                    @click="fetchMembers"
                    class="w-full py-3 text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-colors"
                >
                    Cargar más...
                </button>

                <div v-if="members.length === 0 && !loading" class="text-center py-12">
                    <span class="text-4xl mb-4 block">👻</span>
                    <p class="text-gray-500 dark:text-gray-400 font-medium transition-colors">Aún no hay nadie por aquí...</p>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
