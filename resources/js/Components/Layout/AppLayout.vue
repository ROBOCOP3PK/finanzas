<template>
    <div class="h-screen h-[-webkit-fill-available] flex flex-col overflow-hidden">
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden overscroll-contain">
            <div class="max-w-full main-content-padding top-content-padding">
                <slot />
            </div>
        </main>

        <!-- Bottom Navigation -->
        <BottomNav />
    </div>
</template>

<script setup>
import BottomNav from './BottomNav.vue';
</script>
