// resources/js/Layouts/AuthenticatedLayout.vue
<script setup>
import { ref } from "vue";
import { Link, Head, usePage } from "@inertiajs/vue3"; // Importa Link para la navegación de Inertia
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
// Importa tu logo o usa texto
// import ApplicationLogo from '@/Components/ApplicationLogo.vue'; // Si tienes un componente logo
const authUser = usePage().props.auth.user;
const sidebarOpen = ref(false); // Para el menú hamburguesa
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-100">
        <!-- Fondo de página gris claro -->
        <Head :title="$page.props.title" />

        <!-- Barra Superior Principal (Móvil) -->
        <header
            v-if="$page.props.auth.user"
            class="bg-white shadow-sm sticky top-0 z-40 md:hidden"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Lado Izquierdo: Icono Menú Hamburguesa -->
                    <div class="flex items-center">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-500 focus:outline-none hover:text-indigo-600 p-2 -ml-2"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>
                    </div>
                    <!-- Centro: Logo -->
                    <div class="flex-shrink-0">
                        <Link :href="route('feed.index')">
                            <span class="text-xl font-bold text-indigo-600"
                                >GymPal</span
                            >
                        </Link>
                    </div>
                    <!-- Lado Derecho: Iconos de Acción -->
                    <div class="flex items-center space-x-2">
                        <Link
                            :href="route('posts.create')"
                            title="Nuevo Post"
                            class="text-gray-500 hover:text-indigo-600 p-1 rounded-full"
                        >
                            <svg
                                class="h-7 w-7"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </Link>
                        <slot name="header_actions" />
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar (Menú Lateral) -->
        <div
            v-if="$page.props.auth.user"
            v-show="sidebarOpen"
            @click.away="sidebarOpen = false"
            class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg z-50 md:hidden transition-transform duration-300 ease-out"
            :class="{
                '-translate-x-full': !sidebarOpen,
                'translate-x-0': sidebarOpen,
            }"
        >
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Menú GymPal
                </h2>
                <nav class="flex flex-col space-y-1">
                    <Link
                        :href="route('profile.edit')"
                        @click="sidebarOpen = false"
                        class="text-gray-700 hover:bg-gray-100 hover:text-indigo-600 p-2 rounded-md"
                        >Editar Perfil</Link
                    >
                    <Link
                        href="#"
                        @click="sidebarOpen = false"
                        class="text-gray-700 hover:bg-gray-100 hover:text-indigo-600 p-2 rounded-md"
                        >Ajustes (Futuro)</Link
                    >
                    <div class="p-2 flex items-center justify-between">
                <span class="text-gray-700 dark:text-gray-300">Modo Oscuro</span>
                <ThemeSwitcher/>
            </div>
                <slot name="header_actions" />
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        @click="sidebarOpen = false"
                        class="text-left w-full text-gray-700 hover:bg-gray-100 hover:text-indigo-600 p-2 rounded-md"
                    >
                        Cerrar Sesión
                    </Link>
                </nav>
            </div>
        </div>
        <!-- Overlay para el sidebar -->
        <div
            v-if="$page.props.auth.user && sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black bg-opacity-25 z-40 md:hidden transition-opacity duration-300"
            :class="{ 'opacity-0': !sidebarOpen, 'opacity-100': sidebarOpen }"
        ></div>

        <main
            class="flex-grow"
            :class="{
                'pt-16 md:pt-0':
                    $page.props.auth.user &&
                    !$page.props.isLoginPage &&
                    !$page.props.isRegisterPage,
            }"
        >
            <slot />
        </main>

        <!-- Barra de Navegación Inferior -->
        <nav
            v-if="$page.props.auth.user"
            class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg md:hidden z-50"
        >
            <div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8">
                <div class="relative flex items-center justify-around h-16">
                    <!-- Icono Inicio -->
                    <Link
                        :href="route('feed.index')"
                        title="Inicio"
                        class="flex-grow flex flex-col items-center justify-center text-center px-1"
                        :class="
                            route().current('feed.index') ||
                            route().current('dashboard')
                                ? 'text-indigo-600'
                                : 'text-gray-500 hover:text-indigo-500'
                        "
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5"
                            />
                        </svg>
                        <span class="text-xs mt-1">Inicio</span>
                    </Link>
                    <!-- Icono Descubrir -->
                    <Link
                        :href="route('discover.index')"
                        title="Descubrir"
                        class="flex-grow flex flex-col items-center justify-center text-center px-1"
                        :class="
                            route().current('discover.index')
                                ? 'text-indigo-600'
                                : 'text-gray-500 hover:text-indigo-500'
                        "
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                            />
                        </svg>
                        <span class="text-xs mt-1">Conectar</span>
                    </Link>
                    <!-- Icono Nuevo Post -->
                    <Link
                        :href="route('posts.create')"
                        title="Nuevo"
                        class="flex-grow flex flex-col items-center justify-center text-center px-1 -mt-1"
                    >
                        <span
                            class="inline-block p-1 bg-indigo-600 rounded-full shadow-md"
                        >
                            <svg
                                class="h-7 w-7 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>
                        </span>
                        <span
                            class="text-xs mt-1 sr-only"
                            :class="
                                route().current('posts.create')
                                    ? 'text-indigo-600'
                                    : 'text-gray-500 hover:text-indigo-500'
                            "
                            >Nuevo</span
                        >
                    </Link>
                    <!-- Icono Chat -->
                    <Link
                        :href="route('chat.index')"
                        title="Chat"
                        class="flex-grow flex flex-col items-center justify-center text-center px-1"
                        :class="
                            route().current('chat.index')
                                ? 'text-indigo-600'
                                : 'text-gray-500 hover:text-indigo-500'
                        "
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3.68-3.091a1.256 1.256 0 00-.86-.317H7.812a2.25 2.25 0 01-2.25-2.25V6.982c0-1.242 1.008-2.25 2.25-2.25h8.574a2.25 2.25 0 012.25 2.25v1.529z"
                            />
                        </svg>
                        <span class="text-xs mt-1">Chat</span>
                    </Link>
                    <!-- Icono Perfil -->
                    <Link
                        :href="$page.props.auth.user ? route('profile.show.public', { user: $page.props.auth.user.username }) : '#'"
                        title="Perfil"
                        class="flex-grow flex flex-col items-center justify-center text-center px-1"
                        :class="route().current('profile.show.public') ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500'"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span class="text-xs mt-1">Perfil</span>
                    </Link>
                </div>
            </div>
        </nav>
        <div v-if="$page.props.auth.user" class="pb-16 md:pb-0"></div>
    </div>
</template>
