<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Indicador de estado offline -->
        <OfflineIndicator v-if="!isGuestRoute" />

        <!-- Esperar a que el router esté listo para evitar parpadeos -->
        <template v-if="isRouterReady">
            <!-- Rutas de invitados (login, register) sin layout -->
            <router-view v-if="isGuestRoute" />

            <!-- Rutas autenticadas con layout completo -->
            <AppLayout v-else>
                <router-view />
            </AppLayout>
        </template>

        <!-- Loading mientras el router se inicializa -->
        <div v-else class="flex items-center justify-center min-h-screen">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppLayout from './Components/Layout/AppLayout.vue';
import OfflineIndicator from './Components/UI/OfflineIndicator.vue';
import { useThemeStore } from './Stores/theme';
import { useConfigStore } from './Stores/config';
import { useOfflineStore } from './Stores/offline';

const route = useRoute();
const router = useRouter();
const themeStore = useThemeStore();
const configStore = useConfigStore();
const offlineStore = useOfflineStore();

const isRouterReady = ref(false);

// Verificar si es una ruta de invitados (login, register)
const isGuestRoute = computed(() => route.meta?.guest === true);

onMounted(async () => {
    // Aplicar tema inmediatamente (sin esperar)
    themeStore.inicializar();

    // Inicializar store offline
    offlineStore.init();

    // Esperar a que el router esté completamente listo
    await router.isReady();
    isRouterReady.value = true;

    // Cargar configuración solo si hay usuario autenticado
    if (!isGuestRoute.value) {
        configStore.cargarConfiguracion();
    }
});

onUnmounted(() => {
    offlineStore.cleanup();
});
</script>
