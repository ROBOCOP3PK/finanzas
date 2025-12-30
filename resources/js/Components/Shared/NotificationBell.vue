<template>
    <button
        @click="$emit('click')"
        class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
    >
        <BellIcon class="w-6 h-6 text-gray-600 dark:text-gray-400" />
        <span
            v-if="count > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full min-w-5 h-5 flex items-center justify-center font-medium px-1"
        >
            {{ count > 99 ? '99+' : count }}
        </span>
    </button>
</template>

<script setup>
import { BellIcon } from '@heroicons/vue/24/outline';

defineProps({
    count: {
        type: Number,
        default: 0
    }
});

defineEmits(['click']);
</script>
