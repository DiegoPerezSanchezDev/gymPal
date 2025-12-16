<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const features = [
    {
        title: 'Encuentra tu Gym',
        description: 'Descubre los mejores gimnasios, parques de calistenia y estudios de yoga cerca de ti.',
        icon: '📍',
    },
    {
        title: 'Conecta con Compañeros',
        description: 'Encuentra personas con tus mismos objetivos y nivel. ¡Nunca más entrenes solo!',
        icon: '🤝',
    },
    {
        title: 'Comparte tu Progreso',
        description: 'Sube tus rutinas, fotos y logros. Motiva a otros y recibe apoyo de la comunidad.',
        icon: '🔥',
    },
];
</script>

<template>
    <Head title="Bienvenido" />
    
    <div class="min-h-screen bg-white dark:bg-gray-900 transition-colors duration-300 selection:bg-indigo-500 selection:text-white">
        <!-- Decoration Background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[30%] -right-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-500/20 blur-3xl animate-pulse delay-700"></div>
            <div class="absolute top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-gradient-to-tr from-pink-500/20 to-orange-500/20 blur-3xl animate-pulse"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header/Nav -->
            <nav class="flex items-center justify-between py-6">
                <div class="flex items-center gap-2">
                     <div class="h-12 w-12">
                        <ApplicationLogo class="w-full h-full" />
                    </div>
                </div>
                
                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('feed.index')"
                        class="px-5 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl transition hover:shadow-lg transform hover:-translate-y-0.5"
                    >
                        Ir al Feed
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden sm:block font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-500/30 transition transform hover:-translate-y-0.5"
                        >
                            Únete Ahora
                        </Link>
                    </template>
                </div>
            </nav>

            <!-- Hero Section -->
            <main class="mt-16 sm:mt-24 lg:mt-32 pb-20">
                <div class="text-center lg:text-left lg:grid lg:grid-cols-2 lg:gap-12 items-center">
                    
                    <!-- Text Content -->
                    <div class="mb-12 lg:mb-0 space-y-8 animate-fade-in-up">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                            </span>
                            <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">La comunidad fitness #1</span>
                        </div>

                        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-gray-900 dark:text-white leading-tight tracking-tight">
                            Encuentra tu <br/>
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 animate-gradient-x">GymPal</span>
                        </h1>
                        
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                            Deja de entrenar solo. Conecta con entusiastas del fitness cerca de ti, comparte tus rutinas y alcanza tus metas acompañado.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link
                                :href="route('register')"
                                class="px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-lg rounded-2xl shadow-xl hover:shadow-2xl hover:bg-gray-800 dark:hover:bg-gray-100 transition-all transform hover:-translate-y-1"
                            >
                                Únete Ahora
                            </Link>
                            <Link
                                href="#features"
                                class="px-8 py-4 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold text-lg rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-all"
                            >
                                Saber más
                            </Link>
                        </div>
                        
                        <!-- Social Proof -->
                        <div class="pt-8 flex items-center justify-center lg:justify-start gap-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                            <div class="flex -space-x-2">
                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-900" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=64&h=64" alt=""/>
                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-900" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=64&h=64" alt=""/>
                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-900" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=64&h=64" alt=""/>
                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-900" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=64&h=64" alt=""/>
                            </div>
                            <p>+200 atletas se han unido hoy</p>
                        </div>
                    </div>

                    <!-- Hero Visual -->
                    <div class="relative mt-12 lg:mt-0 perspective-1000 group">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-pink-500 rounded-3xl blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-500"></div>
                        <div class="relative bg-gray-900 rounded-3xl p-4 shadow-2xl border border-gray-800 transform rotate-y-12 rotate-x-6 group-hover:rotate-y-0 group-hover:rotate-x-0 transition-transform duration-700 ease-out">
                            <!-- Mockup Content mimicking the app feed -->
                            <div class="bg-black/50 rounded-2xl overflow-hidden aspect-[9/16] sm:aspect-auto sm:h-[600px] relative">
                                <!-- Status Bar Mock -->
                                <div class="h-6 w-full flex justify-between px-4 items-center text-[10px] text-white/70">
                                    <span>9:41</span>
                                    <div class="flex gap-1">
                                        <span>📶</span>
                                        <span>🔋</span>
                                    </div>
                                </div>
                                <!-- App Header -->
                                <div class="px-4 py-3 flex justify-between items-center border-b border-white/10">
                                    <span class="font-bold text-white text-lg">GymPal</span>
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-yellow-400 to-pink-500"></div>
                                </div>
                                <!-- Feed Item Mock -->
                                <div class="p-4 space-y-4">
                                    <div class="bg-gray-800/80 rounded-xl p-3 border border-white/5 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-gray-700"></div>
                                            <div>
                                                <div class="h-3 w-24 bg-gray-600 rounded"></div>
                                                <div class="h-2 w-16 bg-gray-700 rounded mt-1"></div>
                                            </div>
                                        </div>
                                        <div class="h-48 w-full bg-gray-700 rounded-lg animate-pulse"></div>
                                        <div class="flex gap-4">
                                            <div class="h-6 w-6 bg-red-500/20 rounded-full"></div>
                                            <div class="h-6 w-6 bg-gray-600 rounded-full"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gray-800/80 rounded-xl p-3 border border-white/5 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-lg">💪</div>
                                            <div class="text-white text-sm font-medium">¡Nueva marca personal!</div>
                                        </div>
                                        <span class="text-green-400 font-bold">+5 kg</span>
                                    </div>
                                </div>
                                
                                <!-- Floating Action Button -->
                                <div class="absolute bottom-6 right-6 h-14 w-14 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-indigo-500/50">
                                    <span class="text-2xl text-white">+</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Features Section -->
            <section id="features" class="py-20 border-t border-gray-100 dark:border-gray-800">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Todo lo que necesitas</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Mas que una app de seguimiento, somos tu compañero de entrenamiento digital.</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="feature in features" :key="feature.title" class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-shadow text-center group">
                        <div class="text-5xl mb-6 transform group-hover:scale-110 transition-transform duration-300">{{ feature.icon }}</div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ feature.title }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
        
        <footer class="bg-gray-50 dark:bg-black py-12 border-t border-gray-100 dark:border-gray-800">
             <div class="max-w-7xl mx-auto px-4 text-center">
                 <!-- <div class="flex items-center justify-center gap-2 mb-4">
                    <Link href="/" class="block h-10 w-10">
                        <ApplicationLogo class="w-full h-full" />
                     </Link>
                 </div> -->
                 <p class="text-sm text-gray-400 mt-2">&copy; {{ new Date().getFullYear() }} GymPal Inc.</p>
             </div>
        </footer>
    </div>
</template>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}
.rotate-y-12 {
    transform: rotateY(-12deg) rotateX(5deg);
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
