<script setup>
import { computed, ref, onMounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useTimeAgo } from '@/composables/useTimeAgo';

// Subcomponentes
import LikesModal from './LikesModal.vue';
import ReportPostModal from './ReportPostModal.vue';
import SharePostModal from './SharePostModal.vue';
import DeletePostModal from './DeletePostModal.vue';
import WorkoutCard from './WorkoutCard.vue';
import WorkoutPreviewCard from './WorkoutPreviewCard.vue';
import PostHeader from './PostHeader.vue';
import PostAchievement from './PostAchievement.vue';
import PostComments from './PostComments.vue';
import PostActions from './PostActions.vue';

const { error: showError, success } = useToast();
const { timeAgo } = useTimeAgo();

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

const emit = defineEmits(['post-deleted']);

// Estado reactivo centralizado
const state = ref({
    isLiked: false,
    localLikesCount: 0,
    isSaved: false,
    isProcessingLike: false,
    isProcessingSave: false,
    submittingComment: false,
    newCommentBody: "",
    showLikesModal: false,
    showReportModal: false,
    showShareModal: false,
    showDeleteModal: false,
    heartPop: false
});

const page = usePage();
const isOwner = computed(() => page.props.auth.user && page.props.auth.user.id === props.post.user.id);

// Computadas de formato
const formattedDate = computed(() => timeAgo(props.post.created_at));

const formattedDistance = computed(() => {
    if (props.post.distance === undefined || props.post.distance === null) return null;
    const dist = parseFloat(props.post.distance);
    if (dist < 1) return `${Math.round(dist * 1000)} m`;
    return `${dist.toFixed(1)} km`;
});

const postImageUrl = computed(() => {
    if (!props.post.image_path) return null;
    if (props.post.image_path.startsWith('http')) return props.post.image_path;
    const path = props.post.image_path.startsWith('/') ? props.post.image_path.substring(1) : props.post.image_path;
    return `/storage/${path}`;
});

// Inicialización
const initializeValues = () => {
    state.value.isLiked = props.post.is_liked ?? false;
    state.value.localLikesCount = props.post.likes_count ?? props.post.likers_count ?? 0;
    state.value.isSaved = props.post.is_saved ?? false;
};

onMounted(initializeValues);
watch(() => props.post, initializeValues, { deep: true });

// Métodos de acción
const toggleLike = async () => {
    if (state.value.isProcessingLike) return;
    state.value.isProcessingLike = true;
    
    const originalIsLiked = state.value.isLiked;
    const originalLikesCount = state.value.localLikesCount;

    state.value.isLiked = !state.value.isLiked;
    state.value.localLikesCount += state.value.isLiked ? 1 : -1;

    if (state.value.isLiked) {
        state.value.heartPop = true;
        setTimeout(() => state.value.heartPop = false, 300);
    }

    try {
        const response = await axios.post(route('posts.like.toggle', props.post.id));
        state.value.isLiked = response.data.is_liked;
        state.value.localLikesCount = response.data.likes_count;
        
        // Sincronizar objeto post original
        if (response.data.latest_likers) props.post.latest_likers = response.data.latest_likers;
        props.post.likes_count = response.data.likes_count;
        props.post.likers_count = response.data.likes_count;
        props.post.is_liked = response.data.is_liked;
    } catch (err) {
        state.value.isLiked = originalIsLiked;
        state.value.localLikesCount = originalLikesCount;
        showError('Error al actualizar el like');
    } finally {
        state.value.isProcessingLike = false;
    }
};

const toggleSave = async () => {
    if (state.value.isProcessingSave) return;
    state.value.isProcessingSave = true;
    
    const originalIsSaved = state.value.isSaved;
    state.value.isSaved = !state.value.isSaved;
    
    try {
        const response = await axios.post(route('posts.save', props.post.id));
        state.value.isSaved = response.data.saved;
        success(response.data.saved ? 'Post guardado correctamente' : 'Post eliminado de guardados');
    } catch (err) {
        state.value.isSaved = originalIsSaved;
        showError('Error al guardar el post');
    } finally {
        state.value.isProcessingSave = false;
    }
};

const submitComment = async () => {
    if (!state.value.newCommentBody.trim()) return;
    state.value.submittingComment = true;
    try {
        const response = await axios.post('/comments', {
            post_id: props.post.id,
            body: state.value.newCommentBody,
        });
        if (!props.post.latest_comments) props.post.latest_comments = [];
        props.post.latest_comments.unshift(response.data);
        if (props.post.latest_comments.length > 2) props.post.latest_comments.pop();
        if (response.data.comments_count !== undefined) props.post.comments_count = response.data.comments_count;
        state.value.newCommentBody = "";
    } catch (err) {
        showError('Error al enviar el comentario');
    } finally {
        state.value.submittingComment = false;
    }
};

const handleDeleteModalClose = () => {
    state.value.showDeleteModal = false;
    emit('post-deleted', props.post.id);
};
</script>

<template>
    <div 
        class="bg-white dark:bg-gray-800 rounded-xl max-w-xl mx-auto transition-colors"
        :class="isDetailView ? 'shadow-none border-none' : 'shadow-lg border border-gray-200/80 dark:border-gray-700'"
    >
        <PostHeader 
            :post="post"
            :isOwner="isOwner"
            :formattedDate="formattedDate"
            :formattedDistance="formattedDistance"
            @share-post="state.showShareModal = true"
            @report-post="state.showReportModal = true"
            @delete-post="state.showDeleteModal = true"
        />

        <p v-if="post.content" class="px-5 pb-4 text-gray-700 dark:text-gray-300 whitespace-pre-line text-[15px] leading-relaxed transition-colors">
            {{ post.content }}
        </p>

        <PostAchievement :post="post" />

        <div v-if="post.workout_log && !postImageUrl" class="px-5 pb-4">
            <WorkoutCard :workoutLog="post.workout_log" />
        </div>

        <div v-if="post.workout && (!postImageUrl || isDetailView) && !post.workout_log" class="px-5 pb-4">
            <WorkoutPreviewCard :workout="post.workout" />
        </div>

        <div v-if="postImageUrl" 
            class="relative overflow-hidden mb-6 flex items-center justify-center select-none" 
            :class="isDetailView ? 'max-h-[250px] bg-transparent' : 'max-h-[400px] bg-gray-100 dark:bg-gray-700 transition-colors'"
        >
            <img :src="postImageUrl" alt="Imagen de la publicación" class="w-full object-contain" :class="isDetailView ? 'max-h-[250px]' : 'max-h-[400px]'"/>
        </div>

        <!-- Sección de Likers -->
        <div v-if="state.localLikesCount > 0 && post.latest_likers?.length" class="px-5 pt-3 pb-2 border-t border-gray-100 dark:border-gray-700 transition-colors text-sm">
            <button @click="state.showLikesModal = true" class="flex items-center group">
                <div class="flex -space-x-2 mr-3">
                    <img v-for="liker in post.latest_likers" :key="liker.id"
                        :src="liker.profile_picture_url ? '/storage/' + liker.profile_picture_url : 'https://ui-avatars.com/api/?name=' + liker.name + '&background=random'"
                        class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800"
                    />
                </div>
                <span class="text-gray-600 dark:text-gray-400 group-hover:text-indigo-600 transition-colors">
                    A <strong>{{ post.latest_likers[0].name }}</strong>
                    <span v-if="post.likers_count > 1"> y {{ post.likers_count - 1 }} más</span> les gusta esto
                </span>
            </button>
        </div>

        <PostActions 
            :post="post"
            :isLiked="state.isLiked"
            :isSaved="state.isSaved"
            :localLikesCount="state.localLikesCount"
            :isProcessingLike="state.isProcessingLike"
            :isProcessingSave="state.isProcessingSave"
            :isDetailView="isDetailView"
            :showCommentAction="showCommentAction"
            :heartPop="state.heartPop"
            @toggle-like="toggleLike"
            @toggle-save="toggleSave"
        />

        <PostComments 
            v-model:newCommentBody="state.newCommentBody"
            :post="post"
            :isDetailView="isDetailView"
            :submittingComment="state.submittingComment"
            @submit-comment="submitComment"
        />

        <!-- Modales -->
        <LikesModal v-if="state.showLikesModal" :postId="post.id" @close="state.showLikesModal = false" />
        <ReportPostModal v-if="state.showReportModal" :post="post" @close="state.showReportModal = false" />
        <DeletePostModal v-if="state.showDeleteModal" :post="post" @close="handleDeleteModalClose" />
        <SharePostModal v-if="state.showShareModal" :post="post" @close="state.showShareModal = false" />
    </div>
</template>


