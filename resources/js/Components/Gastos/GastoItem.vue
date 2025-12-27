<template>
    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <div
                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                :style="{ backgroundColor: gasto.categoria?.color + '20' }"
            >
                <span class="text-lg">{{ getEmoji(gasto.categoria?.icono) }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900 dark:text-white truncate">{{ gasto.concepto }}</p>
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ formatDate(gasto.fecha) }}</span>
                    <span>•</span>
                    <span>{{ gasto.medio_pago?.nombre }}</span>
                </div>
            </div>
        </div>
        <div class="text-right flex-shrink-0 ml-2">
            <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(gasto.valor) }}</p>
            <Badge :variant="getTipoBadgeVariant(gasto.tipo)">
                {{ getTipoLabel(gasto.tipo) }}
            </Badge>
        </div>
    </div>
</template>

<script setup>
import Badge from '../UI/Badge.vue';
import { useConfigStore } from '../../Stores/config';

const props = defineProps({
    gasto: {
        type: Object,
        required: true
    }
});

const configStore = useConfigStore();

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};

const getTipoLabel = (tipo) => configStore.getNombreTipo(tipo);

const getTipoBadgeVariant = (tipo) => {
    const variants = {
        'persona_1': 'info',
        'persona_2': 'primary',
        'casa': 'success'
    };
    return variants[tipo] || 'default';
};

const getEmoji = (icono) => {
    const emojis = {
        'utensils': '🍽️',
        'car': '🚗',
        'zap': '⚡',
        'film': '🎬',
        'heart-pulse': '❤️',
        'more-horizontal': '📦'
    };
    return emojis[icono] || '💰';
};
</script>
