<template>
    <div
        v-if="cantidad > 0"
        class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <ExclamationTriangleIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                <div>
                    <p class="font-medium text-yellow-800 dark:text-yellow-200">
                        {{ cantidad }} gasto{{ cantidad > 1 ? 's' : '' }} recurrente{{ cantidad > 1 ? 's' : '' }} pendiente{{ cantidad > 1 ? 's' : '' }}
                    </p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        Toca para revisar y registrar
                    </p>
                </div>
            </div>
            <Button size="sm" @click="$emit('registrar')">
                Registrar
            </Button>
        </div>
    </div>
</template>

<script setup>
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import Button from '../UI/Button.vue';

defineProps({
    cantidad: {
        type: Number,
        default: 0
    }
});

defineEmits(['registrar']);
</script>
