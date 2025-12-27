<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GoogleButton from '@/Components/GoogleButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrarse - GymPal" />

        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2 tracking-tight">Únete a GymPal 🚀</h1>
            <p class="text-gray-500 dark:text-gray-400">Entrena, comparte y crece con amigos.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="Nombre completo" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.name"
                        placeholder="Nombre completo"
                        required
                        autofocus
                        autocomplete="name"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="username" value="Usuario" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <TextInput
                        id="username"
                        type="text"
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.username"
                        placeholder="Nombre de usuario (@usuario)"
                        required
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.username" />
            </div>

            <div>
                <InputLabel for="email" value="Correo electrónico" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
                        autocomplete="email"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.email" />
            </div>

            <div>
                 <InputLabel for="password" value="Contraseña" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.password"
                        placeholder="Contraseña (mín. 8 caracteres)"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="sr-only" />
                <div class="relative">
                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.password_confirmation"
                        placeholder="Repite la contraseña"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2 gap-4 flex flex-col">
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
                        Creando cuenta...
                    </span>
                    <span v-else>Crear cuenta</span>
                </PrimaryButton>
                
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-400 text-xs uppercase font-bold tracking-wider">O regístrate con</span>
                    <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                </div>

                <GoogleButton :href="route('auth.google')" text="Registrarse con Google" />
            </div>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    ¿Ya tienes cuenta?
                    <Link :href="route('login')" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">
                        Inicia sesión
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
