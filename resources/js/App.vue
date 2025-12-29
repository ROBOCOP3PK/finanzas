<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Rutas de invitados (login, register) sin layout -->
        <router-view v-if="isGuestRoute" />

        <!-- Rutas autenticadas con layout completo -->
        <AppLayout v-else>
            <router-view />
        </AppLayout>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from './Components/Layout/AppLayout.vue';
import { useThemeStore } from './Stores/theme';

const route = useRoute();
const themeStore = useThemeStore();

// Verificar si es una ruta de invitados (login, register)
const isGuestRoute = computed(() => route.meta?.guest === true);

onMounted(() => {
    themeStore.inicializar();
});
</script>
