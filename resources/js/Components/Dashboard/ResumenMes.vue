<template>
    <Card title="Resumen del Mes">
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ nombrePersona1 }}</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosPersona1) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">{{ nombrePersona2 }}</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosPersona2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Casa</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(gastosCasa) }}</span>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900 dark:text-white">Total</span>
                    <span class="font-bold text-lg text-gray-900 dark:text-white">{{ formatCurrency(totalGastos) }}</span>
                </div>
            </div>
            <div v-if="totalAbonos > 0" class="flex justify-between items-center text-green-600 dark:text-green-400">
                <span>Abonos</span>
                <span class="font-medium">{{ formatCurrency(totalAbonos) }}</span>
            </div>
        </div>
    </Card>
</template>

<script setup>
import Card from '../UI/Card.vue';

const props = defineProps({
    nombrePersona1: { type: String, default: 'Persona 1' },
    nombrePersona2: { type: String, default: 'Persona 2' },
    gastosPersona1: { type: Number, default: 0 },
    gastosPersona2: { type: Number, default: 0 },
    gastosCasa: { type: Number, default: 0 },
    totalAbonos: { type: Number, default: 0 }
});

const totalGastos = props.gastosPersona1 + props.gastosPersona2 + props.gastosCasa;

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(value);
};
</script>
