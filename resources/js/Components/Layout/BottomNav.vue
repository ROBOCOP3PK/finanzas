<template>
    <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50 safe-x">
        <div class="flex justify-around items-center h-16">
            <router-link
                v-for="item in navItems"
                :key="item.to"
                :to="item.to"
                class="flex flex-col items-center justify-center flex-1 h-full py-2 transition-colors touch-target"
                :class="[
                    isActive(item.to)
                        ? 'text-primary dark:text-indigo-400'
                        : 'text-gray-500 dark:text-gray-400 active:text-gray-700 dark:active:text-gray-300'
                ]"
            >
                <component :is="item.icon" class="w-6 h-6" />
                <span class="text-xs mt-1 font-medium">{{ item.label }}</span>
            </router-link>
        </div>
        <!-- Safe area spacer for home indicator -->
        <div class="safe-bottom bg-white dark:bg-gray-800"></div>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { HomeIcon, PlusCircleIcon, ClockIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

const route = useRoute();

const navItems = [
    { to: '/gastos/nuevo', label: 'Nuevo', icon: PlusCircleIcon },
    { to: '/dashboard', label: 'Resumen', icon: HomeIcon },
    { to: '/historial', label: 'Historial', icon: ClockIcon },
    { to: '/configuracion', label: 'Config', icon: Cog6ToothIcon },
];

const isActive = (path) => {
    return route.path.startsWith(path);
};
</script>
