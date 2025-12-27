<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4">
        <div class="w-full max-w-md">
            <!-- Logo / Titulo -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary dark:text-indigo-400">
                    Finanzas
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Control de gastos compartidos
                </p>
            </div>

            <!-- Card de Registro -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 text-center">
                    Crear Cuenta
                </h2>

                <form @submit.prevent="handleRegister" class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre
                        </label>
                        <input
                            v-model="name"
                            type="text"
                            required
                            autocomplete="name"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:ring-2 focus:ring-primary focus:border-transparent
                                   transition-colors"
                            placeholder="Tu nombre"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Correo electronico
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
                            Contrasena
                        </label>
                        <input
                            v-model="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:ring-2 focus:ring-primary focus:border-transparent
                                   transition-colors"
                            placeholder="Minimo 6 caracteres"
                        />
                    </div>

                    <!-- Confirmar Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Confirmar Contrasena
                        </label>
                        <input
                            v-model="passwordConfirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:ring-2 focus:ring-primary focus:border-transparent
                                   transition-colors"
                            placeholder="Repite la contrasena"
                        />
                    </div>

                    <!-- Error -->
                    <div v-if="authStore.error" class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg">
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
                        {{ authStore.loading ? 'Creando cuenta...' : 'Crear Cuenta' }}
                    </button>
                </form>

                <!-- Link a Login -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Ya tienes cuenta?
                        <router-link to="/login" class="text-primary dark:text-indigo-400 hover:underline font-medium">
                            Inicia sesion
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
import { useThemeStore } from '../Stores/theme';

const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');

onMounted(() => {
    themeStore.inicializar();

    if (authStore.isAuthenticated) {
        router.push('/');
    }
});

const handleRegister = async () => {
    const result = await authStore.register(
        name.value,
        email.value,
        password.value,
        passwordConfirmation.value
    );

    if (result.success) {
        await nextTick();
        window.location.href = '/';
    }
};
</script>
