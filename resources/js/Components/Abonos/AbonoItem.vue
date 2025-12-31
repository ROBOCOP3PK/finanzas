<template>
    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 bg-green-100 dark:bg-green-900/30">
                <BanknotesIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900 dark:text-white truncate">{{ abono.concepto }}</p>
                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ formatDate(abono.fecha) }}</span>
                </div>
            </div>
        </div>
        <div class="text-right flex-shrink-0 ml-2">
            <p class="font-semibold text-green-600 dark:text-green-400">+{{ formatCurrency(abono.valor) }}</p>
            <Badge variant="success">Abono</Badge>
        </div>
    </div>
</template>

<script setup>
import { BanknotesIcon } from '@heroicons/vue/24/outline';
import Badge from '../UI/Badge.vue';
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    abono: {
        type: Object,
        required: true
    }
});

const { formatCurrency } = useCurrency();

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};
</script>
