<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
const props = defineProps({ post: Object, isOwner: Boolean });
const isOpen = ref(false);
const menuRef = ref(null);
const emit = defineEmits(['delete-post', 'report-post']);
onClickOutside(menuRef, () => isOpen.value = false);

function deletePost() {
    emit('delete-post');
    isOpen.value = false;
}

function reportPost() {
    emit('report-post');
    isOpen.value = false;
}

</script>
<template>
    <div class="relative" ref="menuRef">
        <button @click="isOpen = !isOpen" class="p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" /></svg>
        </button>
        <transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95 translate-y-2" enter-to-class="transform opacity-100 scale-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="transform opacity-100 scale-100 translate-y-0" leave-to-class="transform opacity-0 scale-95 translate-y-2">
            <div v-if="isOpen" class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-20 overflow-hidden">
                <div class="py-2">
                    <button v-if="isOwner" @click="deletePost" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors font-medium">
                        <div class="p-2 bg-red-100 rounded-lg text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <span>Eliminar publicación</span>
                    </button>
                    <button v-else @click="reportPost" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 flex items-center gap-3 transition-colors font-medium group" role="menuitem">
                        <div class="p-2 bg-gray-100 rounded-lg text-gray-500 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <span>Denunciar publicación</span>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>