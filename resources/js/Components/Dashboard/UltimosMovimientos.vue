<template>
    <Card title="Últimos Movimientos">
        <div v-if="movimientos.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
            No hay movimientos recientes
        </div>
        <div v-else class="space-y-3">
            <div
                v-for="mov in movimientos"
                :key="`${mov.tipo_movimiento}-${mov.id}`"
                class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
            >
                <div class="flex items-center gap-3">
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center',
                            mov.tipo_movimiento === 'abono'
                                ? 'bg-green-100 dark:bg-green-900'
                                : 'bg-gray-100 dark:bg-gray-700'
                        ]"
                    >
                        <ArrowDownIcon v-if="mov.tipo_movimiento === 'abono'" class="w-5 h-5 text-green-600 dark:text-green-400" />
                        <ArrowUpIcon v-else class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ mov.concepto }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ formatDate(mov.fecha) }}</span>
                            <Badge v-if="mov.categoria" :customColor="mov.categoria_color">
                                {{ mov.categoria }}
                            </Badge>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p
                        :class="[
                            'font-semibold',
                            mov.tipo_movimiento === 'abono'
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-gray-900 dark:text-white'
                        ]"
                    >
                        {{ mov.tipo_movimiento === 'abono' ? '+' : '' }}{{ formatCurrency(mov.valor) }}
                    </p>
                    <p v-if="mov.tipo" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ getTipoLabel(mov.tipo) }}
                    </p>
                </div>
            </div>
        </div>
    </Card>
</template>

<script setup>
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline';
import Card from '../UI/Card.vue';
import Badge from '../UI/Badge.vue';
import { useConfigStore } from '../../Stores/config';

const props = defineProps({
    movimientos: {
        type: Array,
        default: () => []
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

const getTipoLabel = (tipo) => {
    return configStore.getNombreTipo(tipo);
};
</script>
