// resources/js/Components/PostCard.vue
<script setup>
import { computed, ref, onMounted, watch, reactive } from "vue";
import { Link, usePage  } from "@inertiajs/vue3";
import axios from 'axios';
import LikesModal from './LikesModal.vue';
import PostActionsMenu from './PostActionsMenu.vue';
import ReportPostModal from './ReportPostModal.vue';
import DeletePostModal from './DeletePostModal.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    isDetailView: {
        type: Boolean,
        default: false,
    },
});

// Propiedad computada para formatear la fecha de forma legible
const formattedDate = computed(() => {
    return new Date(props.post.created_at).toLocaleString('es-ES', {
        day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit'
    });
});

const isLiked = ref(false);
const localLikesCount = ref(0);
const isProcessingLike = ref(false);
const heartPop = ref(false);

const newCommentBody = ref("");
const submittingComment = ref(false);
const showLikesModal = ref(false);

const page = usePage();
const isOwner = computed(() => page.props.auth.user && page.props.auth.user.id === props.post.user.id);

const showReportModal = ref(false);
const showDeleteModal = ref(false);

function handleDeleteModalClose() {
    showDeleteModal.value = false;
    // Emitir evento para que el padre/array pueda eliminar este post si hace falta
    emit('post-deleted', props.post.id);
}

const emit = defineEmits(['delete-post']);
// Función para inicializar valores
const initializeValues = () => {
    isLiked.value = props.post.is_liked || false;
    localLikesCount.value = props.post.likes_count || props.post.likers_count || 0;
};

// Inicializar valores cuando el componente se monta
onMounted(() => {
    initializeValues();
});

// Reaccionar a cambios en las props
watch(() => props.post, () => {
    initializeValues();
}, { deep: true });

const toggleLike = async () => {
    if (isProcessingLike.value) return;
    isProcessingLike.value = true;
    const originalIsLiked = isLiked.value;
    const originalLikesCount = localLikesCount.value;

    // Solo actualización optimista local
    isLiked.value = !isLiked.value;
    localLikesCount.value = originalLikesCount + (isLiked.value ? 1 : -1);

    try {
        const response = await axios.post(route('posts.like.toggle', props.post.id));
        // Sincronización con backend
        isLiked.value = response.data.is_liked;
        localLikesCount.value = response.data.likes_count;
        if (response.data.latest_likers) {
            props.post.latest_likers = response.data.latest_likers;
        }
        if (response.data.likes_count !== undefined) {
            props.post.likers_count = response.data.likes_count;
        }
        if (response.data.is_liked !== undefined) {
            props.post.is_liked = response.data.is_liked;
        }
    } catch (error) {
        isLiked.value = originalIsLiked;
        localLikesCount.value = originalLikesCount;
        alert('Error al actualizar el like. Inténtalo de nuevo.');
    } finally {
        isProcessingLike.value = false;
    }
};

const submitComment = async () => {
    if (!newCommentBody.value.trim()) return;
    submittingComment.value = true;
    try {
        const response = await axios.post('/comments', {
            post_id: props.post.id,
            body: newCommentBody.value,
        });
        // Si no hay array de latest_comments lo creamos
        if (!props.post.latest_comments) props.post.latest_comments = [];
        props.post.latest_comments.unshift(response.data);
        if (props.post.latest_comments.length > 2) {
            props.post.latest_comments.pop();
        }
        if (response.data.comments_count !== undefined) {
            props.post.comments_count = response.data.comments_count;
        }
        newCommentBody.value = "";
    } catch (err) {
        console.error('Error al enviar comentario', err);
    } finally {
        submittingComment.value = false;
    }
};
</script>

<template>
    <div class="bg-white shadow-lg rounded-xl max-w-xl mx-auto border border-gray-200/80">
        
        <!-- ================== Encabezado del Post ================== -->
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('profile.show.public', { user: post.user.username })">
                    <img
                        :src="post.user.profile_picture_url || 'https://ui-avatars.com/api/?name=' + post.user.name + '&background=random'"
                        alt="Avatar del usuario"
                        class="w-11 h-11 rounded-full object-cover ring-2 ring-gray-100"
                    />
                </Link>
                <div class="ml-3">
                    <Link :href="route('profile.show.public', { user: post.user.username })" class="font-bold text-sm text-gray-800 hover:underline">
                        {{ post.user.name }}
                    </Link>
                    <p class="text-xs text-gray-500">{{ formattedDate }}</p>
                </div>
            </div>
            <PostActionsMenu 
                :post="post" 
                :isOwner="isOwner"
                @report-post="showReportModal = true"
                @delete-post="showDeleteModal = true"
            />
        </div>

        <!-- ================== Contenido del Post ================== -->
        <p v-if="post.content" class="px-5 pb-4 text-gray-700 whitespace-pre-line text-[15px] leading-relaxed">
            {{ post.content }}
        </p>

        <div v-if="post.image_path" class="bg-gray-100 max-h-[70vh] overflow-hidden">
            <img :src="'/storage/' + post.image_path" alt="Imagen de la publicación" class="w-full h-full object-cover"/>
        </div>

        <!-- ================== Lista de Comentarios ================== -->
        <div v-if="!isDetailView && post.latest_comments && post.latest_comments.length" class="px-5 pb-4 pt-2">
            <div class="mb-2 text-sm text-gray-600 font-semibold">
                Comentarios
            </div>
            <ul>
                <li v-for="comment in post.latest_comments" :key="comment.id" class="flex mb-2">
                    <img :src="comment.user && comment.user.profile_picture_url ? comment.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + (comment.user ? comment.user.name : 'Anon') + '&background=random'" 
                        alt="Avatar" 
                        class="w-8 h-8 rounded-full object-cover mr-3" />
                    <div>
                        <span class="font-semibold text-gray-800 mr-2">{{ comment.user ? comment.user.name : 'Anon' }}</span>
                        <span class="text-gray-700">{{ comment.body }}</span>
                    </div>
                </li>
            </ul>
            <Link
                v-if="post.comments_count > (post.latest_comments.length || 0)"
                :href="route('posts.show', post.id)"
                class="text-indigo-600 hover:underline text-sm font-medium mt-1 block"
            >
                Ver más comentarios
            </Link>
        </div>
        <!-- ================== Formulario para nuevo comentario SOLO si NO es vista detalle ================== -->
        <form v-if="!isDetailView" @submit.prevent="submitComment" class="px-5 pb-4">
            <div class="flex items-start gap-3">
                <img 
                    v-if="$page.props.auth && $page.props.auth.user"
                    :src="$page.props.auth.user.profile_picture_url || 'https://ui-avatars.com/api/?name=' + $page.props.auth.user.name + '&background=random'"
                    alt="Tu avatar"
                    class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-100 mt-1 transition-all duration-200"
                />
                <div class="flex-1">
                    <textarea
                        v-model="newCommentBody"
                        rows="2"
                        placeholder="Escribe un comentario..."
                        class="w-full rounded-lg border border-gray-200 p-2 min-h-[45px] text-[15px] transition-colors duration-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 shadow resize-none"
                        :disabled="submittingComment"
                    ></textarea>
                    <div class="flex justify-end mt-1">
                        <button
                            type="submit"
                            class="bg-indigo-500 hover:bg-indigo-600 disabled:bg-indigo-300 text-white rounded-lg px-5 py-2 font-semibold transition-colors duration-200 shadow disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="!newCommentBody.trim() || submittingComment"
                        >
                            Comentar
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- 1. NUEVA SECCIÓN VISUAL DE LIKES -->
        <div v-if="localLikesCount > 0 && post.latest_likers && post.latest_likers.length > 0" 
            class="px-5 pt-3 pb-2 border-t border-gray-100"
        >
            <button @click="showLikesModal = true" class="flex items-center w-full text-left group focus:outline-none">
                <!-- Contenedor de avatares apilados -->
                <div class="flex flex-shrink-0 -space-x-2">
                    <img v-for="liker in post.latest_likers" :key="liker.id"
                        :src="liker.profile_picture_url || 'https://ui-avatars.com/api/?name=' + liker.name + '&background=random'"
                        :title="liker.name"
                        alt="Avatar"
                        class="w-6 h-6 rounded-full object-cover border-2 border-white group-hover:border-indigo-200 transition-all duration-200"
                    />
                </div>
                
                <!-- Texto descriptivo -->
                <span class="ml-3 text-sm text-gray-600 group-hover:text-indigo-600 transition-colors">
                    A 
                    <strong class="font-semibold">{{ post.latest_likers[0].name }}</strong>
                    <span v-if="post.likers_count > 1">
                        y <strong>{{ post.likers_count - 1 }} {{ post.likers_count - 1 === 1 ? 'gympals más' : 'gympals más' }}</strong>
                    </span>
                    les gusta esto.
                </span>
            </button>
        </div>
        
        <!-- ================== Acciones del Post ================== -->
        <div class="px-2 py-1 flex justify-around items-center border-t border-gray-100">
            
            <button 
                @click="toggleLike" 
                :disabled="isProcessingLike"
                class="action-button group disabled:opacity-70 disabled:cursor-not-allowed" 
                :class="{ 'text-red-500': isLiked, 'text-gray-500 hover:text-red-500': !isLiked }"
            >
                <svg 
                    class="w-7 h-7 transition-transform duration-150"
                    :class="[{'transform scale-125': heartPop, 'fill-red-500': isLiked}, isLiked ? 'transform' : '', !isLiked ? 'group-hover:scale-110' : '']"
                    :fill="isLiked ? 'currentColor' : 'none'"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 010-6.364z"></path>
                </svg>
                <!-- <span class="text-sm font-semibold ml-1">{{ localLikesCount }}</span> -->
            </button>

            
            <Link v-if="!isDetailView" :href="route('posts.show', post.id)" class="action-button group text-gray-500 hover:text-indigo-500">
                <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-sm font-semibold ml-1">{{ post.comments_count ?? 0 }}</span>
            </Link>
            
            <button v-if="!isDetailView" class="action-button group text-gray-500 hover:text-green-500">
                <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342A8.963 8.963 0 018 12.001c0-1.01.198-1.968.563-2.835m7.899 5.578A8.963 8.963 0 0116 12.001c0-1.01.198-1.968.563-2.835m0 5.67a8.965 8.965 0 01-7.899 0m7.899 0l-1.42 1.42m-5.058-8.54l1.42-1.42" /></svg>
                <span class="text-sm font-semibold ml-1">Compartir</span>
            </button>
        </div>
        <LikesModal v-if="showLikesModal" :postId="post.id" @close="showLikesModal = false" />
        <ReportPostModal v-if="showReportModal" :post="post" @close="showReportModal = false" />
        <DeletePostModal
            v-if="showDeleteModal"
            :post="post"
            @close="handleDeleteModalClose"
        />
    </div>
</template>

<style scoped>
/* Estilos específicos si los necesitas */
.whitespace-pre-line {
    white-space: pre-line; 
}
.action-button {
    @apply flex items-center justify-center w-full px-3 py-2 rounded-lg transition-colors duration-200 focus:outline-none hover:bg-gray-100;
}
</style>
