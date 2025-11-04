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
        <button @click="isOpen = !isOpen" class="text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" /></svg>
        </button>
        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <div v-if="isOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                <div class="py-1">
                    <button v-if="isOwner" @click="deletePost" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center gap-3">
                    <!-- Icono Papelera --> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> <span>Eliminar</span>
                    </button>
                    <button v-else @click="reportPost" class="w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 hover:text-yellow-900 flex items-center gap-3" role="menuitem">
                    <!-- Icono de Bandera -->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6a2 2 0 012 2v12M3 21h6m0 0h6m-6 0v-4m0 4H3m3-4h6m-6 0V7m6 4v10m6-10V7a2 2 0 00-2-2h-6a2 2 0 00-2 2v10m12 0h-6"></path>
                    </svg>
                    <span>Denunciar</span>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>