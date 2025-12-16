<template>
  <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6 overflow-x-auto whitespace-nowrap scrollbar-hide">
      <div v-if="loading" class="flex gap-4 animate-pulse px-2">
           <div v-for="i in 5" :key="i" class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
      </div>
      
      <div v-else class="flex gap-4 px-2">
          <!-- Mi Historia (siempre primero) -->
          <StoryBubble 
              :image="$page.props.auth.user.profile_picture_url ? '/storage/' + $page.props.auth.user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + $page.props.auth.user.name"
              :name="'Tú'"
              :isMe="true"
              :hasStories="myStories.length > 0"
              :hasUnseen="false" 
              @click="handleMyStoryClick"
          />

          <!-- Historias de Amigos -->
          <StoryBubble 
              v-for="(userStory, index) in friendStories" 
              :key="userStory.user.id"
              :image="userStory.user.avatar_url || 'https://ui-avatars.com/api/?name=' + userStory.user.name"
              :name="userStory.user.name"
              :hasUnseen="userStory.has_unseen"
              :isMe="false"
              :hasStories="true"
              @click="openViewer(index)"
          />

          <div v-if="friendStories.length === 0 && myStories.length === 0" class="flex items-center text-sm text-gray-400 pl-4">
              <span>¡Sube una historia para empezar! 👉</span>
          </div>
      </div>

      <!-- Modales -->
      <CreateStoryModal :show="showCreateModal" @close="closeCreateModal" />
      
      <StoryViewer 
          v-if="showViewer"
          :show="showViewer"
          :storiesData="allStoriesPayload" 
          :initialUserIndex="viewerInitialIndex"
          @close="closeViewer"
          @create="openCreateFromViewer"
      />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import StoryBubble from './StoryBubble.vue';
import CreateStoryModal from './CreateStoryModal.vue';
import StoryViewer from './StoryViewer.vue';
import axios from 'axios';

const loading = ref(true);
const stories = ref([]);
const showCreateModal = ref(false);
const showViewer = ref(false);
const viewerInitialIndex = ref(0);

const page = usePage();
const authUserId = page.props.auth.user.id;

// Separar mis historias de las de amigos
const myStories = computed(() => {
    const me = stories.value.find(s => s.user.id === authUserId);
    return me ? me.stories : [];
});

const friendStories = computed(() => {
    return stories.value.filter(s => s.user.id !== authUserId);
});

// Payload combinado para el visor (primero yo si tengo, luego amigos)
const allStoriesPayload = computed(() => {
    const mePayload = stories.value.find(s => s.user.id === authUserId);
    const friendsPayload = stories.value.filter(s => s.user.id !== authUserId);
    return mePayload ? [mePayload, ...friendsPayload] : friendsPayload;
});

async function fetchStories() {
    try {
        const res = await axios.get(route('stories.index'));
        stories.value = res.data;
    } catch (e) {
        console.error("Error fetching stories", e);
    } finally {
        loading.value = false;
    }
}

function handleMyStoryClick() {
    if (myStories.value.length > 0) {
        // Ver mi historia (índice 0 en allStoriesPayload si existo)
        viewerInitialIndex.value = 0;
        showViewer.value = true;
    } else {
        // Crear historia
        showCreateModal.value = true;
    }
}

function openViewer(friendIndex) {
    // Calcular índice real en allStoriesPayload
    // Si yo estoy en la lista (index 0), el amigo es index + 1
    const amIPresent = myStories.value.length > 0;
    viewerInitialIndex.value = amIPresent ? friendIndex + 1 : friendIndex;
    showViewer.value = true;
}

function closeCreateModal() {
    showCreateModal.value = false;
    fetchStories(); // Recargar para ver la nueva
}

function closeViewer() {
    showViewer.value = false;
    fetchStories(); // Recargar para actualizar "vistos"
}

function openCreateFromViewer() {
    showViewer.value = false;
    setTimeout(() => {
        showCreateModal.value = true;
    }, 100); // Pequeño delay para suavidad
}

onMounted(() => {
    fetchStories();
});
</script>
<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
