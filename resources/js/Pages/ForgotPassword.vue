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

            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <!-- Paso 1: Ingresar Email -->
                <template v-if="step === 1">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 text-center">
                        Recuperar Contrasena
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 text-center">
                        Ingresa tu correo y te enviaremos un codigo
                    </p>

                    <form @submit.prevent="handleSendCode" class="space-y-4">
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

                        <!-- Error -->
                        <div v-if="authStore.error" class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">
                                {{ authStore.error }}
                            </p>
                        </div>

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
                            {{ authStore.loading ? 'Enviando...' : 'Enviar codigo' }}
                        </button>
                    </form>
                </template>

                <!-- Paso 2: Ingresar Codigo y Nueva Contrasena -->
                <template v-else>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 text-center">
                        Nueva Contrasena
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 text-center">
                        Ingresa el codigo enviado a {{ email }}
                    </p>

                    <form @submit.prevent="handleResetPassword" class="space-y-4">
                        <!-- Codigo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Codigo de verificacion
                            </label>
                            <input
                                v-model="code"
                                type="text"
                                required
                                maxlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                       focus:ring-2 focus:ring-primary focus:border-transparent
                                       transition-colors text-center text-2xl tracking-widest font-mono"
                                placeholder="000000"
                            />
                        </div>

                        <!-- Nueva Contrasena -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nueva contrasena
                            </label>
                            <div class="relative">
                                <input
                                    v-model="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-primary focus:border-transparent
                                           transition-colors"
                                    placeholder="Minimo 6 caracteres"
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

                        <!-- Confirmar Contrasena -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Confirmar contrasena
                            </label>
                            <div class="relative">
                                <input
                                    v-model="passwordConfirmation"
                                    :type="showPasswordConfirm ? 'text' : 'password'"
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-primary focus:border-transparent
                                           transition-colors"
                                    placeholder="Repite tu contrasena"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirm = !showPasswordConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <EyeIcon v-if="!showPasswordConfirm" class="w-5 h-5" />
                                    <EyeSlashIcon v-else class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-if="authStore.error" class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">
                                {{ authStore.error }}
                            </p>
                        </div>

                        <!-- Exito -->
                        <div v-if="success" class="p-3 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg">
                            <p class="text-sm text-green-600 dark:text-green-400">
                                Contrasena actualizada correctamente
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="authStore.loading || success"
                            class="w-full py-3 px-4 bg-primary hover:bg-indigo-700 disabled:bg-gray-400
                                   text-white font-medium rounded-lg transition-colors
                                   flex items-center justify-center gap-2"
                        >
                            <svg v-if="authStore.loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ authStore.loading ? 'Actualizando...' : 'Cambiar contrasena' }}
                        </button>

                        <!-- Reenviar codigo -->
                        <div class="text-center">
                            <button
                                type="button"
                                @click="handleResendCode"
                                :disabled="authStore.loading || resendCooldown > 0"
                                class="text-sm text-primary dark:text-indigo-400 hover:underline disabled:text-gray-400"
                            >
                                {{ resendCooldown > 0 ? `Reenviar codigo (${resendCooldown}s)` : 'Reenviar codigo' }}
                            </button>
                        </div>

                        <!-- Volver -->
                        <div class="text-center">
                            <button
                                type="button"
                                @click="goBack"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:underline"
                            >
                                Usar otro correo
                            </button>
                        </div>
                    </form>
                </template>

                <!-- Link a Login -->
                <div class="mt-6 text-center border-t border-gray-200 dark:border-gray-700 pt-4">
                    <router-link to="/login" class="text-sm text-primary dark:text-indigo-400 hover:underline font-medium">
                        Volver a iniciar sesion
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../Stores/auth';
import { useThemeStore } from '../Stores/theme';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();

const step = ref(1);
const email = ref('');
const code = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const success = ref(false);
const resendCooldown = ref(0);
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

let cooldownInterval = null;

onMounted(() => {
    themeStore.inicializar();
    authStore.clearError();

    if (authStore.isAuthenticated) {
        router.push('/');
    }
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

const startCooldown = () => {
    resendCooldown.value = 60;
    cooldownInterval = setInterval(() => {
        resendCooldown.value--;
        if (resendCooldown.value <= 0) {
            clearInterval(cooldownInterval);
        }
    }, 1000);
};

const handleSendCode = async () => {
    const result = await authStore.forgotPassword(email.value);

    if (result.success) {
        step.value = 2;
        startCooldown();
    }
};

const handleResetPassword = async () => {
    if (password.value !== passwordConfirmation.value) {
        authStore.error = 'Las contrasenas no coinciden';
        return;
    }

    const result = await authStore.resetPassword(
        email.value,
        code.value,
        password.value,
        passwordConfirmation.value
    );

    if (result.success) {
        success.value = true;
        setTimeout(() => {
            router.push('/login');
        }, 2000);
    }
};

const handleResendCode = async () => {
    const result = await authStore.forgotPassword(email.value);
    if (result.success) {
        startCooldown();
    }
};

const goBack = () => {
    step.value = 1;
    code.value = '';
    password.value = '';
    passwordConfirmation.value = '';
    authStore.clearError();
};
</script>
