<template>
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-40">
            <div class="px-4 py-3 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Finanzas Compartidas
                </h1>
                <ThemeToggle />
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
import BottomNav from './BottomNav.vue';
import ThemeToggle from './ThemeToggle.vue';
</script>
