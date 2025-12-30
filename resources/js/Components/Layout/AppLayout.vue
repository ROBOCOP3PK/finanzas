<template>
    <div class="h-screen h-[-webkit-fill-available] flex flex-col overflow-hidden">
        <!-- Floating Actions -->
        <div class="fixed top-0 right-0 z-50 safe-top safe-right">
            <div class="flex items-center gap-3 p-4">
                <ThemeToggle />
                <button
                    @click="handleLogout"
                    class="p-2.5 rounded-xl text-gray-500 hover:text-red-500 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm transition-colors touch-target flex items-center justify-center"
                    title="Cerrar sesion"
                >
                    <ArrowRightOnRectangleIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden overscroll-contain">
            <div class="max-w-full main-content-padding pt-14">
                <slot />
            </div>
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
