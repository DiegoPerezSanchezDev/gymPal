<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer Contraseña - GymPal" />

        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2 tracking-tight">Nueva Contraseña 🔑</h1>
            <p class="text-gray-500 dark:text-gray-400">Elige una contraseña segura para tu cuenta.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
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
                        class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3 opacity-75"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        readonly
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva Contraseña" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="pl-10 pr-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.password"
                        placeholder="Nueva Contraseña"
                        required
                        autocomplete="new-password"
                    />
                    <button 
                        v-if="form.password.length > 0"
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-indigo-500 transition-colors"
                    >
                        <svg v-if="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                    </button>
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
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        class="pl-10 pr-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3"
                        v-model="form.password_confirmation"
                        placeholder="Confirmar Nueva Contraseña"
                        required
                        autocomplete="new-password"
                    />
                    <button 
                        v-if="form.password_confirmation.length > 0"
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-indigo-500 transition-colors"
                    >
                        <svg v-if="!showPasswordConfirmation" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                    </button>
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
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
                        Actualizando...
                    </span>
                    <span v-else>Restablecer Contraseña</span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
