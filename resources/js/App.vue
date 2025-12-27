<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <AppLayout>
            <router-view />
        </AppLayout>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import AppLayout from './Components/Layout/AppLayout.vue';
import { useThemeStore } from './Stores/theme';

const themeStore = useThemeStore();

onMounted(() => {
    themeStore.inicializar();
});
</script>
