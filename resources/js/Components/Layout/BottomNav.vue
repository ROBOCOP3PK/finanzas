<template>
    <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 safe-bottom z-50">
        <div class="flex justify-around items-center h-16">
            <router-link
                v-for="item in navItems"
                :key="item.to"
                :to="item.to"
                class="flex flex-col items-center justify-center w-full h-full transition-colors"
                :class="[
                    isActive(item.to)
                        ? 'text-primary dark:text-indigo-400'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                ]"
            >
                <component :is="item.icon" class="w-6 h-6" />
                <span class="text-xs mt-1">{{ item.label }}</span>
            </router-link>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { HomeIcon, PlusCircleIcon, ClockIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

const route = useRoute();

const navItems = [
    { to: '/', label: 'Inicio', icon: HomeIcon },
    { to: '/gastos/nuevo', label: 'Nuevo', icon: PlusCircleIcon },
    { to: '/historial', label: 'Historial', icon: ClockIcon },
    { to: '/configuracion', label: 'Config', icon: Cog6ToothIcon },
];

const isActive = (path) => {
    if (path === '/') {
        return route.path === '/';
    }
    return route.path.startsWith(path);
};
</script>
