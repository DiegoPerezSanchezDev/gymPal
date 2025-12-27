<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GoogleButton from '@/Components/GoogleButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión - GymPal" />

        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2 tracking-tight">¡Bienvenido de nuevo! 👋</h1>
            <p class="text-gray-500 dark:text-gray-400">Tu comunidad de fitness te espera.</p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Correo electrónico" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.email"
                        placeholder="tucorreo@ejemplo.com"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Recordarme</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <div class="space-y-4">
                <PrimaryButton
                    class="w-full justify-center py-3.5 text-base font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transform hover:-translate-y-0.5 transition-all shadow-lg rounded-xl"
                    :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                         <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Iniciando...
                    </span>
                    <span v-else>Iniciar Sesión</span>
                </PrimaryButton>
                
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-400 text-xs uppercase font-bold tracking-wider">O inicia con</span>
                    <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                </div>

                <GoogleButton :href="route('auth.google')" text="Iniciar sesión con Google" />
            </div>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    ¿No tienes cuenta?
                    <Link :href="route('register')" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">
                        Regístrate
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
