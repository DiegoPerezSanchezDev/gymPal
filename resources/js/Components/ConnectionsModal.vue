<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
    connections: {
        type: Array,
        required: true,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['close']);

const authUser = usePage().props.auth.user;
const showDisconnectModal = ref(false);
const userToDisconnect = ref(null);

const openDisconnectModal = (user) => {
    userToDisconnect.value = user;
    showDisconnectModal.value = true;
};

const confirmDisconnect = () => {
    if (userToDisconnect.value?.pivot?.id) {
        router.delete(route('connections.destroy', userToDisconnect.value.pivot.id), {
            preserveScroll: true,
            onFinish: () => {
                showDisconnectModal.value = false;
                userToDisconnect.value = null;
                emit('close');
            }
        });
    }
};

// Como voy a editar el controlador de nuevo, asumamos que tenemos connection_id.
// O mejor, creemos una ruta auxiliar o usemos la lógica de "buscar conexión" en el backend.
// Pero lo más limpio es pivot.

// En el modal, mostraremos la lista.
</script>

<template>
    <div @click.self="$emit('close')" class="fixed inset-0 bg-black bg-opacity-40 dark:bg-opacity-60 z-50 flex items-center justify-center p-4 transition-colors">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 relative animate-fadeInUp transition-colors">
            <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl leading-5 transition-colors">&times;</button>
            
            <h2 class="text-xl font-bold mb-4 pb-3 text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 transition-colors">Conexiones</h2>
            
            <ul v-if="connections.length > 0" class="space-y-1 mb-2 max-h-80 overflow-y-auto custom-scrollbar">
                <li v-for="user in connections" :key="user.id" class="flex items-center justify-between gap-3 p-2 rounded-lg transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                    <div class="flex items-center gap-3">
                        <img :src="user.profile_picture_url ? '/storage/' + user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'" alt="avatar" class="w-10 h-10 rounded-full object-cover" />
                        <div>
                            <Link :href="route('profile.show.public', { user: user.username })" class="font-semibold text-gray-800 dark:text-white hover:underline block transition-colors">
                                {{ user.name }}
                            </Link>
                            <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors">@{{ user.username }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Chat Button -->
                        <Link v-if="authUser && authUser.id !== user.id" :href="route('chat.show', { user: user.username })" 
                            class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-full transition-colors" title="Enviar mensaje">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </Link>

                        <!-- Disconnect Button (Only for own profile) -->
                        <button v-if="isOwnProfile && user.pivot && user.pivot.id" 
                                @click="openDisconnectModal(user)"
                                class="p-2 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-full transition-colors" title="Desconectar">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </li>
            </ul>
            
            <div v-else class="text-gray-500 dark:text-gray-400 text-center p-8 transition-colors">Este usuario aún no tiene conexiones.</div>
        </div>

        <!-- Modal de confirmación de desconexión -->
        <ConfirmModal
            :show="showDisconnectModal"
            type="warning"
            title="¿Desconectar?"
            :message="userToDisconnect ? `¿Estás seguro de que quieres desconectar de ${userToDisconnect.name}?` : ''"
            confirm-text="Sí, desconectar"
            cancel-text="Cancelar"
            @confirm="confirmDisconnect"
            @cancel="showDisconnectModal = false; userToDisconnect = null;"
        />
    </div>
</template>

<style scoped>
.animate-fadeInUp {
    animation: fadeInUp .22s cubic-bezier(0.35,1.15,0.5,1.04);
}
@keyframes fadeInUp {
    from { opacity:0; transform: translateY(24px); }
    to { opacity:1; transform: translateY(0); }
}
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-track { background: #374151; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }
</style>
