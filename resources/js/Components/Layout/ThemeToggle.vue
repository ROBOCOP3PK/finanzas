<template>
    <button
        @click="toggleTheme"
        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        :title="themeStore.tema === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    >
        <SunIcon v-if="themeStore.temaActivo === 'dark'" class="w-5 h-5" />
        <MoonIcon v-else class="w-5 h-5" />
    </button>
</template>

<script setup>
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline';
import { useThemeStore } from '../../Stores/theme';

const themeStore = useThemeStore();

const toggleTheme = () => {
    const newTheme = themeStore.temaActivo === 'dark' ? 'light' : 'dark';
    themeStore.setTema(newTheme);
};
</script>
