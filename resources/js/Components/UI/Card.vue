<template>
    <div
        :class="[
            'bg-white dark:bg-gray-800 rounded-xl shadow-sm',
            padding ? 'p-4' : '',
            border ? 'border border-gray-200 dark:border-gray-700' : ''
        ]"
    >
        <div v-if="title || $slots.action" class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
            </div>
            <slot name="action" />
        </div>
        <slot />
    </div>
</template>

<script setup>
defineProps({
    title: String,
    subtitle: String,
    padding: {
        type: Boolean,
        default: true
    },
    border: {
        type: Boolean,
        default: false
    }
});
</script>
