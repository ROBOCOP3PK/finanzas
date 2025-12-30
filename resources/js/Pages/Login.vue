<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4 overflow-hidden">
        <div class="w-full max-w-md min-w-0">
            <!-- Logo / Título -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary dark:text-indigo-400">
                    Finanzas
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Control de gastos compartidos
                </p>
            </div>

            <!-- Card de Login -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 text-center">
                    Iniciar Sesión
                </h2>

                <form @submit.prevent="handleLogin" class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Correo electrónico
                        </label>
                        <input
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:ring-2 focus:ring-primary focus:border-transparent
                                   transition-colors"
                            placeholder="tu@email.com"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                       focus:ring-2 focus:ring-primary focus:border-transparent
                                       transition-colors"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                                <EyeSlashIcon v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Error -->
                    <div v-show="authStore.error" class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">
                            {{ authStore.error }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="authStore.loading"
                        class="w-full py-3 px-4 bg-primary hover:bg-indigo-700 disabled:bg-gray-400
                               text-white font-medium rounded-lg transition-colors
                               flex items-center justify-center gap-2"
                    >
                        <svg v-if="authStore.loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ authStore.loading ? 'Ingresando...' : 'Ingresar' }}
                    </button>
                </form>

                <!-- Link a Recuperar Contrasena -->
                <div class="mt-4 text-center">
                    <router-link to="/forgot-password" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-indigo-400 hover:underline">
                        Olvidaste tu contrasena?
                    </router-link>
                </div>

                <!-- Link a Registro -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        No tienes cuenta?
                        <router-link to="/register" class="text-primary dark:text-indigo-400 hover:underline font-medium">
                            Registrate aqui
                        </router-link>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                App personal de finanzas compartidas
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../Stores/auth';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);

onMounted(() => {
    // Si ya está autenticado, redirigir
    if (authStore.isAuthenticated) {
        router.push('/');
    }
});

const handleLogin = async () => {
    const result = await authStore.login(email.value, password.value);

    if (result.success) {
        await nextTick();
        window.location.href = '/';
    }
};
</script>
