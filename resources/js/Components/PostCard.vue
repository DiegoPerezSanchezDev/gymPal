// resources/js/Components/PostCard.vue
<script setup>
import { computed, ref, onMounted, watch, reactive } from "vue";
import { Link, usePage  } from "@inertiajs/vue3";
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import LikesModal from './LikesModal.vue';
import PostActionsMenu from './PostActionsMenu.vue';
import ReportPostModal from './ReportPostModal.vue';
import SharePostModal from './SharePostModal.vue';
import DeletePostModal from './DeletePostModal.vue';
import WorkoutCard from './WorkoutCard.vue';
import WorkoutPreviewCard from './WorkoutPreviewCard.vue';

const { error: showError } = useToast();

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    isDetailView: {
        type: Boolean,
        default: false,
    },
    showCommentAction: {
        type: Boolean,
        default: true,
    },
});

// Propiedad computada para formatear la fecha de forma legible
const formattedDate = computed(() => {
    return new Date(props.post.created_at).toLocaleString('es-ES', {
        day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit'
    });
});

const formattedDistance = computed(() => {
    if (props.post.distance === undefined || props.post.distance === null) return null;
    const dist = parseFloat(props.post.distance);
    if (dist < 1) return `${Math.round(dist * 1000)} m`;
    return `${dist.toFixed(1)} km`;
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

const postImageUrl = computed(() => {
    if (!props.post.image_path) return null;
    if (props.post.image_path.startsWith('http')) return props.post.image_path;
    // Asegurarse de no duplicar slash
    const path = props.post.image_path.startsWith('/') ? props.post.image_path.substring(1) : props.post.image_path;
    return `/storage/${path}`;
});

const showReportModal = ref(false);
const showShareModal = ref(false);
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

    // Activar animación de "pop"
    if (isLiked.value) {
        heartPop.value = true;
        setTimeout(() => heartPop.value = false, 300);
    }

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
    } catch (err) {
        isLiked.value = originalIsLiked;
        localLikesCount.value = originalLikesCount;
        showError('Error al actualizar el like');
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
    <div 
        class="bg-white rounded-xl max-w-xl mx-auto"
        :class="isDetailView ? 'shadow-none border-none' : 'shadow-lg border border-gray-200/80'"
    >
        
        <!-- ================== Encabezado del Post ================== -->
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('profile.show.public', { user: post.user.username })">
                    <img
                        :src="post.user.profile_picture_url ? '/storage/' + post.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + post.user.name + '&background=random'"
                        alt="Avatar del usuario"
                        class="w-11 h-11 rounded-full object-cover ring-2 ring-gray-100"
                    />
                </Link>
                <div class="ml-3">
                    <Link :href="route('profile.show.public', { user: post.user.username })" class="font-bold text-sm text-gray-800 hover:underline">
                        {{ post.user.name }}
                    </Link>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500">{{ formattedDate }}</p>
                        <span v-if="formattedDistance" class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            A {{ formattedDistance }}
                        </span>
                    </div>
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

        <!-- ================== Tarjeta de Workout Log (si existe y NO hay imagen) ================== -->
        <div v-if="post.workout_log && !postImageUrl" class="px-5 pb-4">
            <WorkoutCard :workoutLog="post.workout_log" />
        </div>

        <!-- ================== Tarjeta de Rutina Adjunta (si existe y NO hay imagen ni workout_log, O si es vista detalle) ================== -->
        <div v-if="post.workout && (!postImageUrl || isDetailView) && !post.workout_log" class="px-5 pb-4">
            <WorkoutPreviewCard :workout="post.workout" />
        </div>

        <div v-if="postImageUrl" class="overflow-hidden mb-6 flex items-center justify-center" :class="isDetailView ? 'max-h-[250px] bg-transparent' : 'max-h-[400px] bg-gray-100'">
            <img :src="postImageUrl" alt="Imagen de la publicación" class="w-full object-contain" :class="isDetailView ? 'max-h-[250px]' : 'max-h-[400px]'"/>
        </div>

        <!-- ================== Lista de Comentarios ================== -->
        <div v-if="!isDetailView && post.latest_comments && post.latest_comments.length" class="px-5 pb-4 pt-4 space-y-3">
            <div class="mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                Comentarios recientes
            </div>
            <div v-for="comment in post.latest_comments" :key="comment.id" class="flex gap-3 group">
                <Link :href="comment.user ? route('profile.show.public', { user: comment.user.username }) : '#'" class="flex-shrink-0">
                    <img :src="comment.user && comment.user.profile_picture_url ? '/storage/' + comment.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + (comment.user ? comment.user.name : 'Anon') + '&background=random'" 
                        alt="Avatar" 
                        class="w-8 h-8 rounded-full object-cover ring-2 ring-transparent group-hover:ring-indigo-100 transition-all" />
                </Link>
                <div class="flex-1 bg-gray-50 rounded-2xl rounded-tl-none px-4 py-2 text-sm hover:bg-gray-100 transition-colors">
                    <div class="flex justify-between items-baseline">
                        <span class="font-bold text-gray-900 mr-2">{{ comment.user ? comment.user.name : 'Anon' }}</span>
                        <!-- <span class="text-[10px] text-gray-400">{{ new Date(comment.created_at).toLocaleDateString() }}</span> -->
                    </div>
                    <p class="text-gray-700 leading-relaxed mt-0.5">{{ comment.body }}</p>
                </div>
            </div>
            
            <Link
                v-if="post.comments_count > (post.latest_comments.length || 0)"
                :href="route('posts.show', post.id)"
                class="text-indigo-600 hover:text-indigo-700 text-sm font-semibold mt-2 block pl-11 hover:underline"
            >
                Ver los {{ post.comments_count }} comentarios
            </Link>
        </div>
        <!-- ================== Formulario para nuevo comentario SOLO si NO es vista detalle ================== -->
        <form v-if="!isDetailView" @submit.prevent="submitComment" class="px-5 pb-4">
            <div class="flex items-start gap-3">
                <img 
                    v-if="$page.props.auth && $page.props.auth.user"
                    :src="$page.props.auth.user.profile_picture_url ? '/storage/' + $page.props.auth.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + $page.props.auth.user.name + '&background=random'"
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
                        :src="liker.profile_picture_url ? '/storage/' + liker.profile_picture_url : 'https://ui-avatars.com/api/?name=' + liker.name + '&background=random'"
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
                    {{ post.likers_count === 1 ? 'le gusta la publicación.' : 'les gusta la publicación.' }}
                </span>
            </button>
        </div>
        
        <!-- ================== Acciones del Post ================== -->
        <div class="px-4 py-3 flex justify-between items-center border-t border-gray-100 gap-4">
            
            <button 
                @click="toggleLike" 
                :disabled="isProcessingLike"
                class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all duration-200 hover:bg-red-50 group disabled:opacity-70" 
                :class="{ 'text-red-500': isLiked, 'text-gray-500': !isLiked }"
            >
                <svg 
                    class="w-6 h-6 transition-transform duration-200"
                    :class="[{'transform scale-125': heartPop, 'fill-red-500': isLiked}, isLiked ? '' : 'group-hover:scale-110']"
                    :fill="isLiked ? 'currentColor' : 'none'"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 010-6.364z"></path>
                </svg>
                <span class="font-bold text-sm" :class="isLiked ? 'text-red-600' : 'text-gray-600 group-hover:text-red-500'">
                    {{ localLikesCount > 0 ? localLikesCount : 'Me gusta' }}
                </span>
            </button>

            
            <Link v-if="!isDetailView || showCommentAction" :href="route('posts.show', post.id)" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all duration-200 hover:bg-indigo-50 group text-gray-500">
                <svg class="w-6 h-6 transition-transform duration-200 group-hover:scale-110 text-gray-500 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="font-bold text-sm text-gray-600 group-hover:text-indigo-600">{{ post.comments_count > 0 ? post.comments_count : 'Comentar' }}</span>
            </Link>
            
            <button @click="showShareModal = true" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all duration-200 bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-md hover:shadow-lg hover:opacity-95 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342A8.963 8.963 0 018 12.001c0-1.01.198-1.968.563-2.835m7.899 5.578A8.963 8.963 0 0116 12.001c0-1.01.198-1.968.563-2.835m0 5.67a8.965 8.965 0 01-7.899 0m7.899 0l-1.42 1.42m-5.058-8.54l1.42-1.42" /></svg>
                <span class="font-bold text-sm">Compartir</span>
            </button>
        </div>
        <LikesModal v-if="showLikesModal" :postId="post.id" @close="showLikesModal = false" />
        <ReportPostModal v-if="showReportModal" :post="post" @close="showReportModal = false" />
        <DeletePostModal
            v-if="showDeleteModal"
            :post="post"
            @close="handleDeleteModalClose"
        />
        <SharePostModal v-if="showShareModal" :post="post" @close="showShareModal = false" />
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
