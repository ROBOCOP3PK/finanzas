<template>
    <Card title="Resumen del Mes">
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Gastos personales</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosPersonal) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Gastos pareja (100%)</span>
                <span class="font-medium text-red-500">{{ formatCurrency(gastosPareja) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Compartidos ({{ porcentajePersona2 }}% pareja)</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosCompartido) }}</span>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900 dark:text-white">Total</span>
                    <span class="font-bold text-lg text-gray-900 dark:text-white">{{ formatCurrency(totalGastos) }}</span>
                </div>
            </div>
            <div v-if="totalAbonos > 0" class="flex justify-between items-center text-green-600 dark:text-green-400">
                <span>Abonos recibidos</span>
                <span class="font-medium">{{ formatCurrency(totalAbonos) }}</span>
            </div>
        </div>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from '../UI/Card.vue';

const props = defineProps({
    gastosPersonal: { type: Number, default: 0 },
    gastosPareja: { type: Number, default: 0 },
    gastosCompartido: { type: Number, default: 0 },
    totalAbonos: { type: Number, default: 0 },
    porcentajePersona2: { type: Number, default: 40 }
});

const totalGastos = computed(() => props.gastosPersonal + props.gastosPareja + props.gastosCompartido);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(value);
};
</script>
