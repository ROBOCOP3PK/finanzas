<template>
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3 safe-area-top">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button
                    @click="$emit('back')"
                    class="p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <ArrowLeftIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                </button>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Datos de</p>
                    <h2 class="font-semibold text-gray-900 dark:text-white">{{ ownerName }}</h2>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 mt-3">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="$emit('tab-change', tab.id)"
                :class="[
                    'flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-colors',
                    activeTab === tab.id
                        ? 'bg-primary text-white'
                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
            >
                {{ tab.label }}
            </button>
        </div>
    </nav>
</template>

<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

defineProps({
    ownerName: {
        type: String,
        default: ''
    },
    activeTab: {
        type: String,
        default: 'nuevo'
    }
});

defineEmits(['back', 'tab-change']);

const tabs = [
    { id: 'nuevo', label: 'Nuevo' },
    { id: 'resumen', label: 'Resumen' },
    { id: 'historial', label: 'Historial' }
];
</script>
