<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const emits = defineEmits(['close']);

const props = defineProps({
    postId: {
        type: [Number, String],
        required: true,
    }
});

const users = ref([]);
const loading = ref(true);
const page = ref(1);
const hasMorePages = ref(true);

async function fetchLikers(p = 1) {
    loading.value = true;
    try {
        const response = await axios.get(route('posts.likes.index', { post: props.postId }), {
            params: { page: p }
        });
        console.log('Respuesta del backend para el modal:', response.data);
        const data = response.data;
        if (data && data.data) {
            if (p > 1) {
                users.value.push(...data.data);
            } else {
                users.value = data.data;
            }
            hasMorePages.value = !!data.next_page_url;
        } else {
            hasMorePages.value = false;
        }
    } catch(e) {
        hasMorePages.value = false;
    }
    loading.value = false;
}

function loadMore() {
    if (hasMorePages.value) {
        page.value++;
        fetchLikers(page.value);
    }
}

onMounted(() => {
    fetchLikers();
});
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative animate-fadeInUp">
      <button @click="$emit('close')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl leading-5">&times;</button>
      <h2 class="text-xl font-bold mb-4 text-gray-800">Les gusta a</h2>
      <div v-if="loading" class="flex flex-col gap-2 mb-4">
        <div v-for="n in 4" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
      </div>
      <ul v-else class="space-y-3 mb-2 max-h-80 overflow-auto pr-2">
        <li v-for="user in users" :key="user.id" class="flex items-center gap-3">
          <img :src="user.profile_picture_url || ('https://ui-avatars.com/api/?name=' + user.name + '&background=random')" alt="avatar" class="w-9 h-9 rounded-full object-cover border" />
          <Link :href="route('profile.show.public', { user: user.username })" class="font-semibold text-gray-800 hover:underline">
            {{ user.name }}
          </Link>
          <span class="text-sm text-gray-400">@{{ user.username }}</span>
        </li>
      </ul>
      <button v-if="hasMorePages && !loading" @click="loadMore" class="mt-3 px-4 py-2 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-semibold transition-colors">Cargar más</button>
      <div v-if="!loading && users.length === 0" class="text-gray-500 text-center mt-7 mb-4">Aún no hay likes.</div>
    </div>
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
</style>
