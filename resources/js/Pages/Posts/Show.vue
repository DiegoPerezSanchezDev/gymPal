<script setup>
import { Link } from '@inertiajs/vue3';
import PostCard from '../../Components/PostCard.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <!-- Contenedor principal con fondo gris para dar contexto -->
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-xl mx-auto px-4">
            
            <!-- Botón de 'Volver' estilizado -->
            <div class="mb-4">
                <Link :href="route('feed.index')" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver al Feed
                </Link>
            </div>

            <!-- El PostCard en modo 'vista de detalle' -->
            <PostCard :post="props.post" :isDetailView="true" />

            <!-- Nueva "Tarjeta" solo para la lista de comentarios -->
            <div class="bg-white shadow-lg rounded-xl mt-6 border border-gray-200/80 p-6">
                
                <!-- Cabecera de la sección -->
                <h2 class="text-xl font-bold mb-4 pb-3 text-gray-800 border-b border-gray-200">
                    Comentarios ({{ post.comments_count || (post.comments ? post.comments.length : 0) }})
                </h2>

                <!-- Lista de comentarios con scroll si hay exceso -->
                <ul v-if="props.post.comments && props.post.comments.length > 0" 
                    class="space-y-1 max-h-[60vh] overflow-y-auto custom-scrollbar -mr-2 pr-2"
                >
                    <li v-for="comment in props.post.comments" :key="comment.id" 
                        class="flex items-start gap-3 p-3 rounded-lg transition-colors hover:bg-gray-50"
                    >
                        <!-- Avatar del comentarista -->
                        <img :src="comment.user && comment.user.profile_picture_url ? comment.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + (comment.user ? comment.user.name : 'Anon') + '&background=random'" 
                            alt="Avatar" 
                            class="w-9 h-9 rounded-full object-cover" 
                        />
                        <!-- Nombre y cuerpo del comentario -->
                        <div class="flex-1">
                            <div>
                                <Link :href="route('profile.show.public', { user: comment.user.username })" class="font-semibold text-sm text-gray-800 hover:underline">
                                    {{ comment.user ? comment.user.name : 'Anónimo' }}
                                </Link>
                            </div>
                            <p class="text-gray-700 text-[15px] whitespace-pre-line mt-1">{{ comment.body }}</p>
                        </div>
                    </li>
                </ul>

                <!-- Mensaje si no hay comentarios -->
                <div v-else class="text-gray-500 text-center p-8">
                    Este post aún no tiene comentarios.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Scrollbar personalizado para consistencia */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9; /* Color de fondo de la pista */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db; /* Color del 'pulgar' del scroll */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af; /* Color al pasar el ratón */
}
</style>