<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    title: String,
    conversations: Object, // Objeto de paginación de Laravel
});

// Un helper computado para verificar si hay conversaciones
const hasConversations = computed(() => {
    return (
        props.conversations &&
        props.conversations.data &&
        props.conversations.data.length > 0
    );
});
</script>

<template>
    <Head :title="title || 'Mis Chats'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ title || "Mis Chats" }}
            </h2>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-if="hasConversations">
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 mb-4"
                            >
                                Tus Conversaciones
                            </h3>
                            <ul role="list" class="divide-y divide-gray-200">
                                <li
                                    v-for="convo in conversations.data"
                                    :key="convo.id"
                                    class="py-4"
                                >
                                    <Link
                                        :href="
                                            convo.other_user_username
                                                ? route('chat.show', {
                                                      user: convo.other_user_username,
                                                  })
                                                : '#'
                                        "
                                        class="flex items-center space-x-3 hover:bg-gray-50 p-2 rounded-md group"
                                        :class="{
                                            'pointer-events-none opacity-50':
                                                !convo.other_user_username,
                                        }"
                                    >
                                        <div class="flex-shrink-0">
                                            <img
                                                class="h-10 w-10 rounded-full object-cover"
                                                :src="convo.chat_avatar"
                                                :alt="convo.chat_title"
                                            />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 truncate"
                                            >
                                                {{ convo.chat_title }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 truncate"
                                            >
                                                <span
                                                    v-if="
                                                        convo.last_message_sender
                                                    "
                                                    >{{
                                                        convo.last_message_sender
                                                    }}:
                                                </span>
                                                {{ convo.last_message_body }}
                                            </p>
                                        </div>
                                        <div
                                            class="text-right text-xs text-gray-400 flex-shrink-0"
                                        >
                                            <p
                                                v-if="
                                                    convo.last_message_at_human
                                                "
                                            >
                                                {{
                                                    convo.last_message_at_human
                                                }}
                                            </p>
                                            <!-- Aquí podrías poner el contador de mensajes no leídos si lo tuvieras -->
                                            <!-- <span v-if="convo.unread_count > 0" class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ convo.unread_count }}
                                            </span> -->
                                        </div>
                                    </Link>
                                </li>
                            </ul>

                            <!-- Paginación (si la necesitas y tienes el componente Pagination) -->
                            <!-- <Pagination class="mt-6" :links="conversations.links" /> -->
                        </div>
                        <div v-else class="text-center py-12">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-3.86 8.25-8.625 8.25S3.75 16.556 3.75 12s3.86-8.25 8.625-8.25S21 7.444 21 12z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                Sin conversaciones
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Aún no has iniciado ninguna conversación.
                            </p>
                            <div class="mt-6">
                                <Link
                                    :href="route('discover.index')"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                >
                                    <svg
                                        class="-ml-0.5 mr-1.5 h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"
                                        />
                                    </svg>
                                    Descubrir usuarios
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Paginación si conversations.data existe y tiene links -->
                <div
                    v-if="
                        conversations &&
                        conversations.data &&
                        conversations.links &&
                        conversations.links.length > 3
                    "
                    class="mt-6"
                >
                    <div class="flex justify-center">
                        <template
                            v-for="(link, key) in conversations.links"
                            :key="key"
                        >
                            <div
                                v-if="link.url === null"
                                class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                                v-html="link.label"
                            />
                            <Link
                                v-else
                                class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                                :class="{
                                    'bg-indigo-500 text-white': link.active,
                                }"
                                :href="link.url"
                                v-html="link.label"
                                preserve-scroll
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
