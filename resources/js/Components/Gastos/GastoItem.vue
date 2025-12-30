<template>
    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <div
                class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                :style="{ backgroundColor: gasto.categoria?.color + '20' }"
            >
                <i v-if="gasto.categoria?.icono" :class="gasto.categoria.icono" :style="{ color: gasto.categoria.color }"></i>
                <span v-else class="w-4 h-4 rounded" :style="{ backgroundColor: gasto.categoria?.color || '#6366F1' }"></span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900 dark:text-white truncate">{{ gasto.concepto }}</p>
                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                    <span>{{ formatDate(gasto.fecha) }}</span>
                    <span v-if="gasto.medio_pago?.nombre">•</span>
                    <span v-if="gasto.medio_pago?.nombre" class="truncate">{{ gasto.medio_pago.nombre }}</span>
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
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    gasto: {
        type: Object,
        required: true
    }
});

const configStore = useConfigStore();
const { formatCurrency } = useCurrency();

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};

const getTipoLabel = (tipo) => configStore.getNombreTipo(tipo);

const getTipoBadgeVariant = (tipo) => {
    const variants = {
        'personal': 'info',
        'pareja': 'primary',
        'compartido': 'success'
    };
    return variants[tipo] || 'default';
};
</script>
