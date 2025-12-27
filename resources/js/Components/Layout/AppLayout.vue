<template>
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-40">
            <div class="px-4 py-3 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Finanzas Compartidas
                </h1>
                <div class="flex items-center gap-2">
                    <ThemeToggle />
                    <button
                        @click="handleLogout"
                        class="p-2 rounded-lg text-gray-500 hover:text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        title="Cerrar sesión"
                    >
                        <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 pb-20">
            <slot />
        </main>

        <!-- Bottom Navigation -->
        <BottomNav />
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';
import BottomNav from './BottomNav.vue';
import ThemeToggle from './ThemeToggle.vue';
import { useAuthStore } from '../../Stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>
