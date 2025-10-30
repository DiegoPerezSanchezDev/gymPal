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
    <div class="max-w-2xl mx-auto py-7 px-4">
        <Link :href="route('feed.index')" class="inline-block mb-6 text-indigo-600 hover:underline font-semibold text-lg">← Volver</Link>
        <PostCard :post="props.post" :isDetailView="true" />

        <div v-if="props.post.comments && props.post.comments.length" class="bg-white shadow rounded-xl p-5 mt-8">
            <div class="mb-4 text-sm text-gray-600 font-semibold">
                Todos los comentarios
            </div>
            <ul>
                <li v-for="comment in props.post.comments" :key="comment.id" class="flex mb-4">
                    <img :src="comment.user && comment.user.profile_picture_url ? comment.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + (comment.user ? comment.user.name : 'Anon') + '&background=random'" 
                         alt="Avatar" 
                         class="w-8 h-8 rounded-full object-cover mr-3" />
                    <div>
                        <span class="font-semibold text-gray-800 mr-2">{{ comment.user ? comment.user.name : 'Anon' }}</span>
                        <span class="text-gray-700">{{ comment.body }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
